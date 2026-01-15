import { useEffect, useState } from 'react';

interface AnimatedCounterProps {
    end: number;
    start?: number;
    duration?: number;
    prefix?: string;
    suffix?: string;
}

export const AnimatedCounter = ({ end, start = 0, duration = 2000, prefix = '', suffix = '' }: AnimatedCounterProps) => {
    const [count, setCount] = useState(start);

    useEffect(() => {
        let startTime: number;
        let animationFrame: number;

        const animate = (timestamp: number) => {
            if (!startTime) startTime = timestamp;
            const progress = Math.min((timestamp - startTime) / duration, 1);

            // Easing function for smooth animation
            const easeOutQuart = 1 - Math.pow(1 - progress, 4);
            const range = end - start;
            setCount(Math.floor(start + easeOutQuart * range));

            if (progress < 1) {
                animationFrame = requestAnimationFrame(animate);
            }
        };

        animationFrame = requestAnimationFrame(animate);
        return () => cancelAnimationFrame(animationFrame);
    }, [end, start, duration]);

    return (
        <span className="tabular-nums">
            {prefix}{count.toLocaleString()}{suffix}
        </span>
    );
};
