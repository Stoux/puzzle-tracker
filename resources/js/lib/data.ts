
export enum PuzzleProgressionStatus {
    STARTED = 'started',
    FINISHED = 'finished',
    ABORTED = 'aborted',
}

export function getStatusLabelFor(status: PuzzleProgressionStatus) {
    switch (status) {
        case PuzzleProgressionStatus.STARTED:
            return 'Begonnen';
        case PuzzleProgressionStatus.FINISHED:
            return 'Afgerond!';
        case PuzzleProgressionStatus.ABORTED:
            return 'Gestopt...';
    }
}
