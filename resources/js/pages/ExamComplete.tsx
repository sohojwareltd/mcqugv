import { Head, router } from '@inertiajs/react';
import { useEffect } from 'react';
import { Trophy, Clock, CheckCircle, ArrowRight, Sparkles, Calendar } from 'lucide-react';
import { Button } from '../components/ui/Button';
import { Card } from '../components/ui/Card';

interface Props {
    token: string;
    resultPublishAt?: string;
}

export default function ExamComplete({ token, resultPublishAt }: Props) {
    const publishDate = resultPublishAt ? new Date(resultPublishAt) : null;
    const now = new Date();
    const isPublished = publishDate ? now >= publishDate : true;

    useEffect(() => {
        // Trigger confetti celebration if available
        if (typeof window !== 'undefined' && (window as any).confetti) {
            const duration = 3000;
            const end = Date.now() + duration;

            const frame = () => {
                (window as any).confetti({
                    particleCount: 3,
                    angle: 60,
                    spread: 55,
                    origin: { x: 0 },
                    colors: ['#4F46E5', '#10B981', '#F59E0B'],
                });
                (window as any).confetti({
                    particleCount: 3,
                    angle: 120,
                    spread: 55,
                    origin: { x: 1 },
                    colors: ['#4F46E5', '#10B981', '#F59E0B'],
                });

                if (Date.now() < end) {
                    requestAnimationFrame(frame);
                }
            };

            frame();
        }
    }, []);

    return (
        <>
            <Head title="Exam Complete - UGV Quiz" />
            <div className="min-h-screen gradient-bg">
                {/* Subtle UGV Branding Bar */}
                <div className="bg-card/50 backdrop-blur-sm border-b border-border/50">
                    <div className="container mx-auto px-4 py-2">
                        <div className="flex items-center justify-center gap-3 text-xs text-muted-foreground">
                            <img 
                                src="/UGV-Logo-02.png" 
                                alt="UGV" 
                                className="h-6 w-auto object-contain opacity-80"
                            />
                            <span className="hidden sm:inline">University of Global Village, Barishal</span>
                            <span className="hidden md:inline">•</span>
                            <a href="tel:01763877777" className="hidden md:inline hover:text-primary transition-colors">
                                01763877777
                            </a>
                        </div>
                    </div>
                </div>

                {/* Subtle Admission Notice */}
                <div className="bg-primary/5 border-b border-primary/10">
                    <div className="container mx-auto px-4 py-2">
                        <div className="flex items-center justify-center gap-2 text-xs text-muted-foreground">
                            <Calendar className="w-3 h-3 text-primary" />
                            <span className="font-bengali" style={{ fontFamily: 'var(--font-bengali)' }}>
                                Winter 2026 Admission Fair: জানুয়ারি ১৪-২৪ | 
                            </span>
                            <span className="text-primary font-semibold">Up to 100% Scholarship</span>
                            <span className="hidden sm:inline">•</span>
                            <a href="https://www.ugv.edu.bd" target="_blank" rel="noopener noreferrer" className="hidden sm:inline hover:text-primary transition-colors">
                                www.ugv.edu.bd
                            </a>
                        </div>
                    </div>
                </div>

                <div className="flex items-center justify-center p-4">
                <div className="max-w-md w-full text-center">
                    {/* Logo */}
                    <div className="inline-flex items-center gap-2 bg-primary/10 px-4 py-2 rounded-full mb-6 animate-slide-up">
                        <Sparkles className="w-4 h-4 text-primary" />
                        <span className="font-semibold text-primary">UGV Quiz</span>
                    </div>

                    {/* Celebration Icon */}
                    <div className="relative mb-8 animate-float">
                        <div className="w-24 h-24 bg-gradient-to-br from-primary to-accent rounded-full flex items-center justify-center mx-auto glow-primary">
                            <CheckCircle className="w-12 h-12 text-primary-foreground" />
                        </div>
                        <div className="absolute -top-2 -right-2 text-4xl">🎉</div>
                    </div>

                    {/* Thank You Message */}
                    <h1 className="text-3xl md:text-4xl font-bold mb-4 animate-slide-up" style={{ animationDelay: '0.1s' }}>
                        Thank You!
                    </h1>
                    <p className="text-lg text-muted-foreground mb-8 animate-slide-up" style={{ animationDelay: '0.2s' }}>
                        Your exam has been submitted successfully.
                    </p>

                    {/* Info Card */}
                    <Card className="p-6 card-shadow border-0 text-left mb-8 animate-scale-in" style={{ animationDelay: '0.3s' }}>
                        <div className="space-y-4">
                            <div className="flex items-center justify-between py-2 border-b border-border">
                                <span className="text-muted-foreground">Status</span>
                                <span className="font-semibold text-success">Completed</span>
                            </div>
                            {publishDate && (
                                <div className="flex items-center justify-between py-2">
                                    <span className="text-muted-foreground flex items-center gap-2">
                                        <Clock className="w-4 h-4" />
                                        Result Published At
                                    </span>
                                    <span className="font-semibold">
                                        {publishDate.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}
                                    </span>
                                </div>
                            )}
                        </div>
                    </Card>

                    {/* Processing Animation */}
                    <div className="flex items-center justify-center gap-3 mb-8 animate-slide-up" style={{ animationDelay: '0.4s' }}>
                        <div className="w-3 h-3 bg-primary rounded-full animate-bounce" style={{ animationDelay: '0s' }} />
                        <div className="w-3 h-3 bg-primary rounded-full animate-bounce" style={{ animationDelay: '0.1s' }} />
                        <div className="w-3 h-3 bg-primary rounded-full animate-bounce" style={{ animationDelay: '0.2s' }} />
                        <span className="text-muted-foreground ml-2">Processing your result...</span>
                    </div>

                    {/* Result Announcement Info */}
                    {publishDate && (
                        <p className="text-muted-foreground mb-6 animate-slide-up" style={{ animationDelay: '0.5s' }}>
                            Your merit position will be published at{' '}
                            <span className="font-semibold text-foreground">
                                {publishDate.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}
                            </span>
                        </p>
                    )}

                    {/* Merit Button */}
                    <Button
                        size="lg"
                        onClick={() => router.visit('/leaderboard')}
                        disabled={!isPublished}
                        className="w-full max-w-xs rounded-xl glow-primary hover:scale-[1.02] transition-all animate-slide-up"
                        style={{ animationDelay: '0.6s' }}
                    >
                        <Trophy className="mr-2 w-5 h-5" />
                        {isPublished ? 'View Merit List' : 'Merit List (Locked)'}
                        <ArrowRight className="ml-2 w-5 h-5" />
                    </Button>

                    {/* Home Link */}
                    <div className="mt-6 animate-slide-up" style={{ animationDelay: '0.7s' }}>
                        <Button variant="ghost" onClick={() => router.visit('/')}>
                            ← Back to Home
                        </Button>
                    </div>
                </div>
                </div>
            </div>
        </>
    );
}
