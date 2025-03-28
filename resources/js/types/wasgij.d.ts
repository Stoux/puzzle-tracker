import {PuzzleProgressionStatus, PuzzleRelationType} from "@/lib/data";

export interface Puzzle {

    id: number;
    sku: string|null;
    puzzle_title: string;
    collection_tag: string|null;
    collection_number: number|null;
    number_of_pieces: number|null;
    number_of_pieces_label: string|null;
    website_label: string|null;
    year: number|null;
    artist: string|null;
    thumbnail: string|null;
    tags: string[],
}

export interface FilterablePuzzle extends Puzzle{
    is_rerelease: boolean;
    purchased: {
        own: boolean,
        anyone: boolean,
        at_me: boolean,
    },
    finished: {
        self: boolean,
        related: boolean,
    }
}

export interface PuzzleDetails extends Puzzle {

    description: string|null;
    webshop_url: string|null;
    images: { preview: string, full: string }[];
    hints: {
        '1': string|null;
        '2': string|null
    };
    dimensions: string|null;

    progressions: PuzzleProgression[],
    purchases: PurchasedPuzzle[],
    relations: DetailedPuzzleRelation[],

    next_in_collection: Puzzle|null,
    previous_in_collection: Puzzle|null,

}

export interface PuzzleProgression {
    id: number;
    user: PuzzleUser;
    when: string|null;
    started_on: string|null;
    completed_on: string|null;
    status: PuzzleProgressionStatus,
    comments: string|null,
    images: {
        id: number,
        comment: string|null,
        url: string;
    }[];
}


export interface PurchasedPuzzle {
    id: number,
    purchased_on: string|null;
    purchased_on_formatted: string|null;
    owner: PuzzleUser;
    currently_at: PuzzleUser|null;
}

export interface PuzzleUser {
    id: number;
    name: string;
}

export interface BarePuzzleRelation {
    id: number;
    puzzle_id: number;
    relates_to_id: number;
    type: PuzzleRelationType;
    comment: string|null;
}

export interface DetailedPuzzleRelation extends BarePuzzleRelation{
    is_source: boolean,
    relates_to: Puzzle,
    progressions: PuzzleProgression[],
    purchases: PurchasedPuzzle[],
}
