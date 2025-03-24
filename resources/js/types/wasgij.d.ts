
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

export interface PuzzleDetails extends Puzzle {

    description: string|null;
    webshop_url: string|null;
    images: { preview: string, full: string }[];
    hints: {
        '1': string|null;
        '2': string|null
    };
    dimensions: string|null;

}
