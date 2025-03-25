export enum PuzzleProgressionStatus {
    STARTED = 'started',
    FINISHED = 'finished',
    ABORTED = 'aborted',
}

export function getStatusLabelFor(status: PuzzleProgressionStatus) {
    switch (status) {
        case PuzzleProgressionStatus.STARTED:
            return 'Begonnen 🚀';
        case PuzzleProgressionStatus.FINISHED:
            return 'Opgelost 🤩';
        case PuzzleProgressionStatus.ABORTED:
            return 'Gestopt 😔';
    }
}

export enum PuzzleRelationType {
    RERELEASE_CHANGED = 'rerelease-changed',
    RERELEASE_NEAR_IDENTICAL = 'rerelease-near-identical',
    RERELEASE_IDENTICAL = 'rerelease-identical',
    RETRO = 'retro',
}


export function getTypeLabelFor(status: PuzzleRelationType, isSource: boolean = true ) {
    if (!isSource) {
        switch(status) {
            case PuzzleRelationType.RETRO:
                return 'Origineel van Retro';
            case PuzzleRelationType.RERELEASE_CHANGED:
                return 'Vorige uitgave (Aangepast)';
            case PuzzleRelationType.RERELEASE_NEAR_IDENTICAL:
                return 'Vorige uitgave (Minimale aanpassingen)';
            case PuzzleRelationType.RERELEASE_IDENTICAL:
                return 'Vorige uitgave (Identiek)';
        }
    }

    switch (status) {
        case PuzzleRelationType.RERELEASE_CHANGED:
            return 'Heruitgave (Aangepast)';
        case PuzzleRelationType.RERELEASE_NEAR_IDENTICAL:
            return 'Heruitgave (Minimale aanpassingen)';
        case PuzzleRelationType.RERELEASE_IDENTICAL:
            return 'Heruitgave (Identiek)';
        case PuzzleRelationType.RETRO:
            return 'Retro editie';

    }
}

export function getAllTypeOptions() {
    return {
        [PuzzleRelationType.RETRO]: getTypeLabelFor(PuzzleRelationType.RETRO),
        [PuzzleRelationType.RERELEASE_IDENTICAL]: getTypeLabelFor(PuzzleRelationType.RERELEASE_IDENTICAL),
        [PuzzleRelationType.RERELEASE_NEAR_IDENTICAL]: getTypeLabelFor(PuzzleRelationType.RERELEASE_NEAR_IDENTICAL),
        [PuzzleRelationType.RERELEASE_CHANGED]: getTypeLabelFor(PuzzleRelationType.RERELEASE_CHANGED),
    };
}
