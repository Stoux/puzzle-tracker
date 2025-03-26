<?php

namespace App\Console\Commands;

use App\Enums\PuzzleRelationType;
use App\Models\Puzzle;
use App\Models\PuzzleRelation;
use Illuminate\Console\Command;

class SuggestRelationsCommand extends Command
{
    protected $signature = 'puzzles:suggest-relations';

    protected $description = 'Attempt to resolve relations between current puzzles & suggest creating the relations';

    public function handle(): void
    {
        // Fetch all puzzles & group by cleaned puzzle title
        $grouped = Puzzle::all()->groupBy(fn( Puzzle $puzzle ) => preg_replace('/[!., ]/', '', strtolower($puzzle->puzzle_title ?? '') ) );

        // Loop through the groups
        foreach ($grouped as $cleanedTitle => $puzzles) {
            // Skip invalid titles or not groups of puzzles
            if (empty($cleanedTitle) || $puzzles->count() <= 1) {
                continue;
            }

            $this->output->writeln(sprintf('= Multiple (%d) puzzles found for: %s', $puzzles->count(), $puzzles[0]->puzzle_title));

            // Order by year of release
            /** @var \Illuminate\Support\Collection<Puzzle> $puzzles */
            $puzzles = $puzzles->sortBy('year', SORT_ASC);

            // Generally means that relations should be in that order
            $releases = [];
            $retros = [];
            foreach($puzzles as $puzzle) {
                if (str_contains($puzzle->collection_tag ?? '', 'Retro')) {
                    $retros[] = $puzzle;
                } else {
                    $releases[] = $puzzle;
                }
            }

            // Map to expected relations
            $relations = [];
            foreach($releases as $index => $release) {
                // I am the OG of any next releases
                for ($i = $index + 1; $i < count($releases); $i++) {
                    $relations[ $release->id . '.' . $releases[$i]->id ] = [ $release, $releases[$i], PuzzleRelationType::RERELEASE_IDENTICAL ];
                }

                // Any release is linked to any retro
                foreach($retros as $retro) {
                    $relations[ $release->id . '.' . $retro->id ] = [ $release, $retro, PuzzleRelationType::RETRO ];
                }
            }

            foreach($retros as $index => $retro) {
                // I am the OG of any next releases
                for ($i = $index + 1; $i < count($retros); $i++) {
                    $relations[ $retro->id . '.' . $retros[$i]->id ] = [ $retro, $retros[$i], PuzzleRelationType::RERELEASE_IDENTICAL ];
                }
            }


            // No relations to check. Should never happen?
            if (empty($relations)) {
                continue;
            }

            // Check if those relations already exist
            $whereRelations = implode(' OR ', array_map(
                fn( array $relation ) => sprintf('(puzzle_id = %d AND relates_to_id = %d)', $relation[0]->id, $relation[1]->id),
                $relations
            ));
            /** @var \Illuminate\Support\Collection<string, PuzzleRelation> $existingRelations */
            $existingRelations = PuzzleRelation::whereRaw($whereRelations)->get()->keyBy(fn(PuzzleRelation $relation) => $relation->puzzle_id . '.' . $relation->relates_to_id);

            // Output the existing relations
            foreach( $existingRelations as $key => $existingRelation) {
                $this->output->writeln(sprintf( '=> Existing %s: %s => %s', $existingRelation->type->name, $this->fmt($existingRelation->puzzle), $this->fmt($existingRelation->relates_to)));
                unset($relations[$key]);
            }

            // All already exist
            if (empty($relations)) {
                $this->info('=> All relations already exist');
                continue;
            }

            // Prompt for missing
            foreach( $relations as $relation ) {
                $this->question(sprintf('=> Missing %s: %s => %s', $relation[2]->name, $this->fmt($relation[0]), $this->fmt($relation[1])));
            }

            if (!$this->confirm('=> Create relations?')) {
                $this->warn('=> Skipping!');
                continue;
            }

            foreach($relations as $relation) {
                PuzzleRelation::create([
                    'puzzle_id' => $relation[0]->id,
                    'relates_to_id' => $relation[1]->id,
                    'type' => $relation[2],
                ]);
            }

            $this->info('=> Created!');
        }
    }

    private function fmt(Puzzle $puzzle): string
    {
        return sprintf('%s %s: %s (Art. %s - %d)', $puzzle->collection_tag, $puzzle->collection_number, $puzzle->puzzle_title ?? '', $puzzle->sku, $puzzle->year);
    }
}
