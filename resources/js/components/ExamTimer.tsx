import { useEffect, useState } from 'react';
import { Clock } from 'lucide-react';

interface ExamTimerProps {
    startTime: Date;
    durationMinutes?: number;
    onTimeUp?: () => void;
}

export const ExamTimer = ({ startTime, durationMinutes = 10, onTimeUp }: ExamTimerProps) => {
    const [timeRemaining, setTimeRemaining] = useState<number>(durationMinutes * 60);

    useEffect(() => {
        const calculateTimeRemaining = () => {
            const elapsed = Math.floor((new Date().getTime() - startTime.getTime()) / 1000);
            const remaining = durationMinutes * 60 - elapsed;
            return Math.max(0, remaining);
        };

        setTimeRemaining(calculateTimeRemaining());

        const timer = setInterval(() => {
            const remaining = calculateTimeRemaining();
            setTimeRemaining(remaining);

            if (remaining <= 0 && onTimeUp) {
                clearInterval(timer);
                onTimeUp();
            }
        }, 1000);

        return () => clearInterval(timer);
    }, [startTime, durationMinutes, onTimeUp]);

    const minutes = Math.floor(timeRemaining / 60);
    const seconds = timeRemaining % 60;
    const isLowTime = timeRemaining <= 60; // Less than 1 minute
    const isCritical = timeRemaining <= 30; // Less than 30 seconds

    return (
        <div
            className={`flex items-center gap-2 px-4 py-2 rounded-full font-semibold transition-all ${
                isCritical
                    ? 'bg-destructive/20 text-destructive animate-pulse'
                    : isLowTime
                      ? 'bg-warning/20 text-warning'
                      : 'bg-primary/10 text-primary'
            }`}
        >
            <Clock className="w-5 h-5" />
            <span className="tabular-nums text-lg">
                {minutes.toString().padStart(2, '0')}:{seconds.toString().padStart(2, '0')}
            </span>
        </div>
    );
};
