import { Head, router } from '@inertiajs/react';
import { useEffect } from 'react';
import { Clock, CheckCircle, ArrowRight, Sparkles, Calendar, Info, Facebook, Award } from 'lucide-react';
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
                {/* UGV Branding Bar */}
                <div className="bg-card/80 backdrop-blur-sm border-b border-border/50 shadow-sm">
                    <div className="container mx-auto px-4 py-3">
                        <div className="flex items-center justify-center gap-4">
                            <a href="https://www.ugv.edu.bd" target="_blank" rel="noopener noreferrer" className="flex items-center gap-3 hover:opacity-80 transition-opacity">
                                <img 
                                    src="/UGV-Logo-02.png" 
                                    alt="University of Global Village" 
                                    className="h-10 md:h-12 w-auto object-contain"
                                />
                                <div className="hidden sm:block text-left">
                                    <div className="text-sm font-semibold text-foreground">University of Global Village</div>
                                    <div className="text-xs text-muted-foreground">Barishal</div>
                                </div>
                            </a>
                            <span className="hidden md:inline text-muted-foreground">•</span>
                            <a href="tel:01763877777" className="hidden md:inline text-sm text-muted-foreground hover:text-primary transition-colors">
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
                    <div className="mb-6 animate-slide-up">
                        <img 
                            src="/UGV-Logo-02.png" 
                            alt="University of Global Village" 
                            className="h-16 md:h-20 w-auto mx-auto mb-4 object-contain"
                        />
                        <div className="inline-flex items-center gap-2 bg-primary/10 px-4 py-2 rounded-full">
                            <Sparkles className="w-4 h-4 text-primary" />
                            <span className="font-semibold text-primary">UGV Quiz</span>
                        </div>
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

                    {/* Result Rules & Instructions */}
                    <Card className="p-6 card-shadow border-0 text-left mb-8 animate-scale-in" style={{ animationDelay: '0.35s' }}>
                        <div className="flex items-start gap-3 mb-4">
                            <Info className="w-5 h-5 text-primary flex-shrink-0 mt-0.5" />
                            <div className="flex-1">
                                <h3 className="font-semibold text-foreground mb-3">Result Publication & Merit Ranking Rules</h3>
                                
                                {/* Result Publication */}
                                <div className="mb-4 p-4 bg-primary/10 rounded-lg border-2 border-primary/30 shadow-sm">
                                    <div className="flex items-start gap-3 mb-2">
                                        <div className="shrink-0 w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center">
                                            <Clock className="w-5 h-5 text-primary" />
                                        </div>
                                        <div className="flex-1">
                                            <p className="text-sm font-bold text-primary mb-2">Result Publication</p>
                                            <p className="text-sm text-foreground leading-relaxed">
                                                Results will be published at <span className="font-bold text-primary text-base">12:00 AM</span> on our{' '}
                                                <a 
                                                    href="https://www.facebook.com/ugvbarisal" 
                                                    target="_blank" 
                                                    rel="noopener noreferrer"
                                                    className="text-primary hover:underline font-semibold inline-flex items-center gap-1.5 underline decoration-2 underline-offset-2"
                                                >
                                                    <Facebook className="w-4 h-4" />
                                                    Facebook Page
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {/* Merit Ranking Rules */}
                                <div className="p-4 bg-accent/10 rounded-lg border-2 border-accent/30 shadow-sm">
                                    <div className="flex items-start gap-3 mb-3">
                                        <div className="shrink-0 w-8 h-8 rounded-full bg-accent/20 flex items-center justify-center">
                                            <Award className="w-5 h-5 text-accent" />
                                        </div>
                                        <div className="flex-1">
                                            <p className="text-sm font-bold text-accent mb-3">Merit Ranking Criteria</p>
                                            <div className="space-y-3 text-sm">
                                                <div className="flex items-start gap-3 p-2 bg-background/50 rounded-md">
                                                    <span className="font-bold text-primary text-base shrink-0">1.</span>
                                                    <span className="text-foreground">Ranked by <strong className="text-primary font-bold">Score</strong> <span className="text-muted-foreground">(Higher score = Better rank)</span></span>
                                                </div>
                                                <div className="flex items-start gap-3 p-2 bg-background/50 rounded-md">
                                                    <span className="font-bold text-primary text-base shrink-0">2.</span>
                                                    <span className="text-foreground">If scores are equal, ranked by <strong className="text-primary font-bold">Exam Completion Time</strong> <span className="text-muted-foreground">(Faster completion = Better rank)</span></span>
                                                </div>
                                                <div className="flex items-start gap-3 p-2 bg-background/50 rounded-md">
                                                    <span className="font-bold text-primary text-base shrink-0">3.</span>
                                                    <div className="flex-1">
                                                        <span className="text-foreground">If still equal, ranked by <strong className="text-primary font-bold">Subject Hierarchy</strong>:</span>
                                                        <div className="mt-2 flex items-center gap-2 flex-wrap">
                                                            <span className="px-3 py-1.5 bg-primary text-primary-foreground font-bold rounded-md text-sm">Math</span>
                                                            <span className="text-primary font-bold">→</span>
                                                            <span className="px-3 py-1.5 bg-primary text-primary-foreground font-bold rounded-md text-sm">English</span>
                                                            <span className="text-primary font-bold">→</span>
                                                            <span className="px-3 py-1.5 bg-primary text-primary-foreground font-bold rounded-md text-sm">Bangla</span>
                                                            <span className="text-primary font-bold">→</span>
                                                            <span className="px-3 py-1.5 bg-primary text-primary-foreground font-bold rounded-md text-sm">ICT</span>
                                                            <span className="text-primary font-bold">→</span>
                                                            <span className="px-3 py-1.5 bg-primary text-primary-foreground font-bold rounded-md text-sm">GK</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Card>

                    {/* Processing Animation */}
                    <div className="flex items-center justify-center gap-3 mb-8 animate-slide-up" style={{ animationDelay: '0.4s' }}>
                        <div className="w-3 h-3 bg-primary rounded-full animate-bounce" style={{ animationDelay: '0s' }} />
                        <div className="w-3 h-3 bg-primary rounded-full animate-bounce" style={{ animationDelay: '0.1s' }} />
                        <div className="w-3 h-3 bg-primary rounded-full animate-bounce" style={{ animationDelay: '0.2s' }} />
                        <span className="text-muted-foreground ml-2">Processing your result...</span>
                    </div>

                    {/* Follow Us on Facebook Button */}
                    <div className="mb-6 animate-slide-up" style={{ animationDelay: '0.5s' }}>
                        <Button
                            size="lg"
                            onClick={() => window.open('https://www.facebook.com/ugvbarisal', '_blank', 'noopener,noreferrer')}
                            className="w-full max-w-xs rounded-xl bg-[#1877F2] hover:bg-[#166FE5] text-white hover:scale-[1.02] transition-all"
                        >
                            <Facebook className="mr-2 w-5 h-5" />
                            Follow Us on Facebook
                            <ArrowRight className="ml-2 w-5 h-5" />
                        </Button>
                    </div>

                    {/* Result Time Message */}
                    <div className="p-4 bg-primary/10 border border-primary/20 rounded-lg mb-6 animate-slide-up" style={{ animationDelay: '0.6s' }}>
                        <div className="flex items-center justify-center gap-2 text-center">
                            <Clock className="w-5 h-5 text-primary" />
                            <p className="text-foreground font-semibold">
                                See your result at <span className="text-primary text-lg">12:00 AM</span> on our Facebook page
                            </p>
                        </div>
                    </div>

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
