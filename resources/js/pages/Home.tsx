import { Head, Link, router } from '@inertiajs/react';
import { Brain, Clock, Trophy, ArrowRight, Sparkles, Users, Medal, Calendar, AlertTriangle, Shield, Ban, Monitor, CheckCircle } from 'lucide-react';
import { Button } from '../components/ui/Button';
import { Card } from '../components/ui/Card';
import { AnimatedCounter } from '../components/AnimatedCounter';
import { CountdownTimer } from '../components/CountdownTimer';

interface Participant {
    rank: number;
    full_name: string;
    hsc_roll: string | null;
}

interface PreviousLeaderboard {
    exam: {
        id: number;
        title: string;
        result_publish_at?: string;
    };
    participants: Participant[];
}

interface HomeProps {
    exam: {
        id: number;
        title: string;
        total_questions: number;
        start_time?: string;
        end_time?: string;
        result_publish_at?: string;
        status: 'upcoming' | 'active' | 'ended';
        next_exam?: {
            id: number;
            title: string;
            start_time: string;
        };
    } | null;
    participantCount: number;
    previousLeaderboard: PreviousLeaderboard | null;
}

export default function Home({ exam, participantCount, previousLeaderboard }: HomeProps) {
    const handleStartExam = () => {
        router.visit('/exam/form');
    };

    const features = [
        {
            icon: Brain,
            title: '20 MCQ',
            description: 'Challenging questions to test your knowledge',
            color: 'text-primary',
            bgColor: 'bg-primary/10',
        },
        {
            icon: Clock,
            title: 'Time-bound',
            description: 'Complete the exam at your own pace',
            color: 'text-accent',
            bgColor: 'bg-accent/10',
        },
        {
            icon: Trophy,
            title: 'Merit-based',
            description: 'Get ranked among thousands of students',
            color: 'text-warning',
            bgColor: 'bg-warning/10',
        },
    ];

    // Determine what timers to show based on exam status
    const getTimerInfo = () => {
        if (!exam) return null;

        const now = new Date();
        
        if (exam.status === 'upcoming' && exam.start_time) {
            return {
                type: 'exam_start' as const,
                targetDate: new Date(exam.start_time),
                label: 'Exam starts in',
            };
        }

        if (exam.status === 'active' && exam.end_time) {
            return {
                type: 'exam_end' as const,
                targetDate: new Date(exam.end_time),
                label: 'Exam ends in',
            };
        }

        if (exam.status === 'ended') {
            const timers = [];
            
            if (exam.next_exam?.start_time) {
                timers.push({
                    type: 'next_exam' as const,
                    targetDate: new Date(exam.next_exam.start_time),
                    label: 'Next exam starts in',
                });
            }
            
            if (exam.result_publish_at) {
                timers.push({
                    type: 'result_publish' as const,
                    targetDate: new Date(exam.result_publish_at),
                    label: 'Results publish in',
                });
            }
            
            return timers.length > 0 ? timers : null;
        }

        return null;
    };

    const timerInfo = getTimerInfo();
    const isMultipleTimers = Array.isArray(timerInfo);

    // Check if previous exam results are published (only show if publish time has passed)
    const isPreviousExamResultsPublished = previousLeaderboard?.exam.result_publish_at
        ? new Date(previousLeaderboard.exam.result_publish_at) <= new Date()
        : false; // If no publish time set, don't show

    const getRankBadge = (rank: number) => {
        if (rank === 1) return <Medal className="w-5 h-5 text-yellow-500" />;
        if (rank === 2) return <Medal className="w-5 h-5 text-gray-400" />;
        if (rank === 3) return <Medal className="w-5 h-5 text-amber-600" />;
        return <span className="text-sm font-bold text-muted-foreground">{rank}</span>;
    };

    return (
        <>
            <Head title="UGV Quiz - National MCQ Exam Platform" />
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

                {/* Hero Section */}
                <div className="container mx-auto px-4 py-12 md:py-20">
                    <div className="text-center max-w-4xl mx-auto">
                        {/* Logo */}
                        <div className="mb-8 animate-slide-up">
                            <img 
                                src="/UGV-Logo-02.png" 
                                alt="University of Global Village" 
                                className="h-16 md:h-20 w-auto mx-auto mb-4 object-contain"
                            />
                            <div className="inline-flex items-center gap-2 bg-primary/10 px-4 py-2 rounded-full">
                                <Sparkles className="w-5 h-5 text-primary" />
                                <span className="font-semibold text-primary">UGV Quiz</span>
                            </div>
                        </div>
                        {/* Main Headline */}
                        <h1 className="text-4xl md:text-6xl font-extrabold mb-6 animate-slide-up" style={{ animationDelay: '0.1s' }}>
                            <span className="text-gradient">Test Your Skill.</span>
                            <br />
                            <span className="text-foreground">Earn Your Merit.</span>
                        </h1>

                        {/* Subtext */}
                        <p className="text-lg md:text-xl text-muted-foreground mb-8 max-w-2xl mx-auto animate-slide-up" style={{ animationDelay: '0.2s' }}>
                        Join the ultimate quiz challenge hosted by UGV – University of Global Village, Southern Bangladesh’s most high-tech uni! 🎉 In celebration of Admission Fair 2026 (Jan 14–24), test your brain, compete with thousands of students across Bangladesh, and win awesome prizes! 🏆✨
                        </p>

                        {/* Stats Row */}
                        <div className="flex flex-wrap justify-center gap-6 md:gap-10 mb-10 animate-slide-up" style={{ animationDelay: '0.3s' }}>
                            <div className="flex items-center gap-2">
                                <Users className="w-5 h-5 text-primary" />
                                <span className="text-2xl font-bold text-foreground">
                                    <AnimatedCounter start={100} end={1238 + participantCount} />
                                </span>
                                <span className="text-muted-foreground">Participants</span>
                            </div>
                            <div className="hidden md:block w-px h-8 bg-border" />
                            <div className="flex items-center gap-2">
                                <Brain className="w-5 h-5 text-accent" />
                                <span className="text-xl font-bold text-foreground">{exam?.total_questions || 20}</span>
                                <span className="text-muted-foreground">Questions</span>
                            </div>
                            <div className="hidden md:block w-px h-8 bg-border" />
                            <div className="flex items-center gap-2">
                                <Clock className="w-5 h-5 text-warning" />
                                <span className="text-xl font-bold text-foreground">10 Minutes</span>
                                <span className="text-muted-foreground">Time</span>
                            </div>
                        </div>

                        {/* CTA Button - Only show if exam is active */}
                        {exam?.status === 'active' && (
                            <Button
                                size="lg"
                                onClick={handleStartExam}
                                className="text-lg px-8 py-6 rounded-full glow-primary hover:scale-105 transition-all duration-300 animate-pulse-glow"
                            >
                                Start Exam Now
                                <ArrowRight className="ml-2 w-5 h-5" />
                            </Button>
                        )}

                        {/* Timer Display */}
                        {timerInfo && (
                            <div className="mt-12 animate-slide-up space-y-6" style={{ animationDelay: '0.4s' }}>
                                {isMultipleTimers ? (
                                    // Multiple timers (for ended exam)
                                    timerInfo.map((timer, index) => (
                                        <div key={index} className="flex justify-center">
                                            <div className="text-center">
                                                <p className="text-muted-foreground mb-4">{timer.label}:</p>
                                                <div className="flex justify-center">
                                                    <CountdownTimer targetDate={timer.targetDate} />
                                                </div>
                                            </div>
                                        </div>
                                    ))
                                ) : (
                                    // Single timer
                                    timerInfo && (
                                        <div className="flex justify-center">
                                            <div className="text-center">
                                                <p className="text-muted-foreground mb-4">{timerInfo.label}:</p>
                                                <div className="flex justify-center">
                                                    <CountdownTimer targetDate={timerInfo.targetDate} />
                                                </div>
                                            </div>
                                        </div>
                                    )
                                )}
                            </div>
                        )}

                        {/* Status Messages */}
                        {exam?.status === 'upcoming' && (
                            <div className="mt-8 p-4 bg-warning/10 border border-warning/20 rounded-lg animate-slide-up" style={{ animationDelay: '0.4s' }}>
                                <p className="text-warning font-semibold">Exam has not started yet. Please wait for the countdown.</p>
                            </div>
                        )}
                        
                        {exam?.status === 'ended' && (
                            <div className="mt-8 p-4 bg-muted border border-border rounded-lg animate-slide-up" style={{ animationDelay: '0.4s' }}>
                                <p className="text-muted-foreground">This exam has ended. Check the timers above for next exam and results.</p>
                            </div>
                        )}
                    </div>
                </div>

                {/* Exam Rules Section */}
                <div className="container mx-auto px-4 pb-12">
                    <div className="max-w-4xl mx-auto mb-12">
                        <Card className="p-6 md:p-8 card-shadow border-0">
                            {/* Warning Banner */}
                            <div className="mb-6 p-4 bg-warning/10 border border-warning/20 rounded-lg flex items-start gap-3">
                                <AlertTriangle className="w-5 h-5 text-warning shrink-0 mt-0.5" />
                                <div>
                                    <h3 className="font-semibold text-warning mb-1">Important Instructions</h3>
                                    <p className="text-sm text-muted-foreground">
                                        Violating any of these rules may result in automatic disqualification. The exam is closely monitored for academic integrity.
                                    </p>
                                </div>
                            </div>

                            {/* Rules List */}
                            <div className="space-y-4 md:space-y-6">
                                {/* Rule 1: Dev Tools */}
                                <div className="flex gap-4">
                                    <div className="shrink-0 w-10 h-10 rounded-full bg-destructive/10 flex items-center justify-center">
                                        <Ban className="w-5 h-5 text-destructive" />
                                    </div>
                                    <div className="flex-1">
                                        <h3 className="font-semibold mb-2">Developer Tools Are Prohibited</h3>
                                        <p className="text-sm text-muted-foreground">
                                            Opening browser developer tools (F12, Inspect Element, or right-click inspect) will trigger a violation warning and may result in exam termination.
                                        </p>
                                    </div>
                                </div>

                                {/* Rule 2: Screenshots */}
                                <div className="flex gap-4">
                                    <div className="shrink-0 w-10 h-10 rounded-full bg-destructive/10 flex items-center justify-center">
                                        <Shield className="w-5 h-5 text-destructive" />
                                    </div>
                                    <div className="flex-1">
                                        <h3 className="font-semibold mb-2">No Screenshots or Screen Sharing</h3>
                                        <p className="text-sm text-muted-foreground">
                                            Taking screenshots or sharing your screen during the exam is strictly prohibited. The system monitors for such activities.
                                        </p>
                                    </div>
                                </div>

                                {/* Rule 3: Tab Switching */}
                                <div className="flex gap-4">
                                    <div className="shrink-0 w-10 h-10 rounded-full bg-warning/10 flex items-center justify-center">
                                        <Monitor className="w-5 h-5 text-warning" />
                                    </div>
                                    <div className="flex-1">
                                        <h3 className="font-semibold mb-2">Stay on Exam Tab</h3>
                                        <p className="text-sm text-muted-foreground">
                                            Switching to another tab, window, or application will be detected. Multiple violations may result in exam termination.
                                        </p>
                                    </div>
                                </div>

                                {/* Rule 4: Time Limit */}
                                <div className="flex gap-4">
                                    <div className="shrink-0 w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                                        <Clock className="w-5 h-5 text-primary" />
                                    </div>
                                    <div className="flex-1">
                                        <h3 className="font-semibold mb-2">10 Minute Time Limit</h3>
                                        <p className="text-sm text-muted-foreground">
                                            You have exactly 10 minutes to complete the exam. The timer will be displayed at the top. Time will automatically expire after 10 minutes.
                                        </p>
                                    </div>
                                </div>

                                {/* Rule 5: Inactivity */}
                                <div className="flex gap-4">
                                    <div className="shrink-0 w-10 h-10 rounded-full bg-warning/10 flex items-center justify-center">
                                        <AlertTriangle className="w-5 h-5 text-warning" />
                                    </div>
                                    <div className="flex-1">
                                        <h3 className="font-semibold mb-2">Active Participation Required</h3>
                                        <p className="text-sm text-muted-foreground">
                                            If you remain inactive (no mouse or keyboard activity) for 30 seconds, a warning will be shown.
                                        </p>
                                    </div>
                                </div>

                                {/* Rule 6: Other Restrictions */}
                                <div className="flex gap-4">
                                    <div className="shrink-0 w-10 h-10 rounded-full bg-accent/10 flex items-center justify-center">
                                        <CheckCircle className="w-5 h-5 text-accent" />
                                    </div>
                                    <div className="flex-1">
                                        <h3 className="font-semibold mb-2">Additional Restrictions</h3>
                                        <ul className="text-sm text-muted-foreground space-y-1 list-disc list-inside">
                                            <li>Right-click is disabled on the exam page</li>
                                            <li>Text selection is disabled</li>
                                            <li>Keyboard shortcuts (Ctrl+C, Ctrl+V, F12, etc.) are restricted</li>
                                            <li>Copy-paste is disabled</li>
                                            <li>Exam must be completed in one session</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </Card>
                    </div>
                </div>

                {/* Features Section */}
                <div className="container mx-auto px-4 pb-12">
                    <div className="grid md:grid-cols-3 gap-6 max-w-4xl mx-auto mb-12">
                        {features.map((feature, index) => (
                            <Card
                                key={feature.title}
                                className="p-6 card-shadow hover:scale-105 transition-all duration-300 border-0 animate-scale-in"
                                style={{ animationDelay: `${0.5 + index * 0.1}s` }}
                            >
                                <div className={`w-14 h-14 rounded-2xl ${feature.bgColor} flex items-center justify-center mb-4`}>
                                    <feature.icon className={`w-7 h-7 ${feature.color}`} />
                                </div>
                                <h3 className="text-xl font-bold mb-2">{feature.title}</h3>
                                <p className="text-muted-foreground">{feature.description}</p>
                            </Card>
                        ))}
                    </div>
                </div>

                {/* Previous Exam Leaderboard */}
                {previousLeaderboard && previousLeaderboard.participants.length > 0 && isPreviousExamResultsPublished && (
                    <div className="container mx-auto px-4 pb-20">
                        <Card className="max-w-4xl mx-auto p-6 md:p-8 card-shadow border-0">
                            <div className="flex items-center gap-3 mb-6">
                                <Trophy className="w-6 h-6 text-warning" />
                                <div>
                                    <h2 className="text-2xl font-bold">Previous Exam Results</h2>
                                    <p className="text-sm text-muted-foreground">{previousLeaderboard.exam.title}</p>
                                </div>
                            </div>
                            <div className="space-y-3">
                                {previousLeaderboard.participants.map((participant) => (
                                    <div
                                        key={participant.rank}
                                        className="flex items-center gap-4 p-4 rounded-xl bg-secondary/50 hover:bg-secondary transition-colors"
                                    >
                                        <div className="flex-shrink-0 w-10 h-10 rounded-full bg-background flex items-center justify-center">
                                            {getRankBadge(participant.rank)}
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <h3 className="font-semibold truncate">{participant.full_name}</h3>
                                            {participant.hsc_roll && (
                                                <p className="text-sm text-muted-foreground truncate">Roll: {participant.hsc_roll}</p>
                                            )}
                                        </div>
                                        <div className="flex-shrink-0 text-right">
                                            <div className="text-lg font-bold text-primary">#{participant.rank}</div>
                                            <div className="text-xs text-muted-foreground">Rank</div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                            <div className="mt-6 text-center">
                                <Button
                                    variant="outline"
                                    onClick={() => router.visit(`/leaderboard`)}
                                >
                                    View Full Leaderboard
                                    <ArrowRight className="ml-2 w-4 h-4" />
                                </Button>
                            </div>
                        </Card>
                    </div>
                )}

                {/* Footer */}
                <footer className="border-t border-border py-8 bg-card/50">
                    <div className="container mx-auto px-4 text-center">
                        <a href="https://www.ugv.edu.bd" target="_blank" rel="noopener noreferrer" className="inline-block mb-4 hover:opacity-80 transition-opacity">
                            <img 
                                src="/UGV-Logo-02.png" 
                                alt="University of Global Village" 
                                className="h-12 w-auto mx-auto object-contain"
                            />
                        </a>
                        <p className="text-muted-foreground text-sm mb-2">© 2024 UGV Quiz. All rights reserved.</p>
                        <p className="text-xs text-muted-foreground">Powered by University of Global Village, Barishal</p>
                    </div>
                </footer>
            </div>
        </>
    );
}
