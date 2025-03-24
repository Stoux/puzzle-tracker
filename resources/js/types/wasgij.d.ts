
export interface Puzzle {

    id: number;
    puzzle_title: string;
    collection_tag: string|null;
    collection_number: number|null;
    number_of_pieces_label: string|null;
    year: number|null;
    artist: string|null;
    thumbnail: string|null;
    tags: string[],
}
