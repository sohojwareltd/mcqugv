import { useEffect, useState } from 'react';

interface CountdownTimerProps {
    targetDate: Date | string;
    onComplete?: () => void;
}

interface TimeLeft {
    hours: number;
    minutes: number;
    seconds: number;
}

export const CountdownTimer = ({ targetDate, onComplete }: CountdownTimerProps) => {
    const [timeLeft, setTimeLeft] = useState<TimeLeft>({ hours: 0, minutes: 0, seconds: 0 });
    const [isComplete, setIsComplete] = useState(false);

    useEffect(() => {
        const calculateTimeLeft = () => {
            const target = typeof targetDate === 'string' ? new Date(targetDate) : targetDate;
            const difference = target.getTime() - new Date().getTime();

            if (difference <= 0) {
                setIsComplete(true);
                onComplete?.();
                return { hours: 0, minutes: 0, seconds: 0 };
            }

            return {
                hours: Math.floor(difference / (1000 * 60 * 60)),
                minutes: Math.floor((difference / 1000 / 60) % 60),
                seconds: Math.floor((difference / 1000) % 60),
            };
        };

        setTimeLeft(calculateTimeLeft());
        const timer = setInterval(() => {
            setTimeLeft(calculateTimeLeft());
        }, 1000);

        return () => clearInterval(timer);
    }, [targetDate, onComplete]);

    if (isComplete) {
        return null;
    }

    const TimeBlock = ({ value, label }: { value: number; label: string }) => (
        <div className="flex flex-col items-center">
            <div className="bg-primary text-primary-foreground rounded-lg px-4 py-3 min-w-[60px] text-center">
                <span className="text-2xl font-bold tabular-nums">
                    {value.toString().padStart(2, '0')}
                </span>
            </div>
            <span className="text-xs text-muted-foreground mt-1 uppercase tracking-wide">{label}</span>
        </div>
    );

    return (
        <div className="flex items-center gap-2">
            <TimeBlock value={timeLeft.hours} label="Hours" />
            <span className="text-2xl font-bold text-primary mb-5">:</span>
            <TimeBlock value={timeLeft.minutes} label="Min" />
            <span className="text-2xl font-bold text-primary mb-5">:</span>
            <TimeBlock value={timeLeft.seconds} label="Sec" />
        </div>
    );
};
