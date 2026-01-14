interface ProgressBarProps {
    current: number;
    total: number;
    showLabel?: boolean;
}

export const ProgressBar = ({ current, total, showLabel = true }: ProgressBarProps) => {
    const percentage = (current / total) * 100;

    return (
        <div className="w-full">
            {showLabel && (
                <div className="flex justify-between text-sm text-muted-foreground mb-2">
                    <span>Progress</span>
                    <span>{current} of {total}</span>
                </div>
            )}
            <div className="h-2 bg-secondary rounded-full overflow-hidden">
                <div
                    className="h-full bg-gradient-to-r from-primary to-accent transition-all duration-500 ease-out rounded-full"
                    style={{ width: `${percentage}%` }}
                />
            </div>
        </div>
    );
};
