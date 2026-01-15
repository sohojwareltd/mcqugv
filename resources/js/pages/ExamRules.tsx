import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { AlertTriangle, Shield, Ban, Monitor, Clock, CheckCircle, ArrowRight, ArrowLeft, Sparkles, Calendar } from 'lucide-react';
import { Button } from '../components/ui/Button';
import { Card } from '../components/ui/Card';

interface ExamRulesProps {
    token: string;
}

export default function ExamRules({ token }: ExamRulesProps) {
    const [accepted, setAccepted] = useState(false);

    const handleStartExam = () => {
        router.visit(`/exam/${token}`);
    };

    return (
        <>
            <Head title="Exam Rules - UGV Quiz" />
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
                <div className="w-full max-w-3xl">
                    {/* Header */}
                    <div className="text-center mb-8">
                        <div className="inline-flex items-center gap-2 bg-primary/10 px-4 py-2 rounded-full mb-4">
                            <Sparkles className="w-4 h-4 text-primary" />
                            <span className="font-semibold text-primary">UGV Quiz</span>
                        </div>
                        <h1 className="text-3xl md:text-4xl font-bold mb-2">Exam Rules & Guidelines</h1>
                        <p className="text-muted-foreground">Please read carefully before starting</p>
                    </div>

                    <Card className="p-6 md:p-8 card-shadow border-0">
                        {/* Warning Banner */}
                        <div className="mb-6 p-4 bg-warning/10 border border-warning/20 rounded-lg flex items-start gap-3">
                            <AlertTriangle className="w-5 h-5 text-warning flex-shrink-0 mt-0.5" />
                            <div>
                                <h3 className="font-semibold text-warning mb-1">Important Instructions</h3>
                                <p className="text-sm text-muted-foreground">
                                    Violating any of these rules may result in automatic disqualification. The exam is closely monitored for academic integrity.
                                </p>
                            </div>
                        </div>

                        {/* Rules List */}
                        <div className="space-y-6 mb-8">
                            {/* Rule 1: Dev Tools */}
                            <div className="flex gap-4">
                                <div className="flex-shrink-0 w-10 h-10 rounded-full bg-destructive/10 flex items-center justify-center">
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
                                <div className="flex-shrink-0 w-10 h-10 rounded-full bg-destructive/10 flex items-center justify-center">
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
                                <div className="flex-shrink-0 w-10 h-10 rounded-full bg-warning/10 flex items-center justify-center">
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
                                <div className="flex-shrink-0 w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
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
                                <div className="flex-shrink-0 w-10 h-10 rounded-full bg-warning/10 flex items-center justify-center">
                                    <AlertTriangle className="w-5 h-5 text-warning" />
                                </div>
                                <div className="flex-1">
                                    <h3 className="font-semibold mb-2">Active Participation Required</h3>
                                    <p className="text-sm text-muted-foreground">
                                        If you remain inactive (no mouse or keyboard activity) for 30 seconds, the current question will be automatically skipped with a warning.
                                    </p>
                                </div>
                            </div>

                            {/* Rule 6: Other Restrictions */}
                            <div className="flex gap-4">
                                <div className="flex-shrink-0 w-10 h-10 rounded-full bg-accent/10 flex items-center justify-center">
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

                        {/* Acceptance Checkbox */}
                        <div className="mb-6 p-4 bg-card border border-border rounded-lg">
                            <label className="flex items-start gap-3 cursor-pointer">
                                <input
                                    type="checkbox"
                                    checked={accepted}
                                    onChange={(e) => setAccepted(e.target.checked)}
                                    className="mt-1 w-5 h-5 rounded border-border text-primary focus:ring-2 focus:ring-primary focus:ring-offset-2"
                                />
                                <span className="text-sm">
                                    <span className="font-semibold">I understand and agree</span> to follow all the exam rules and guidelines. I acknowledge that violating any rule may result in disqualification.
                                </span>
                            </label>
                        </div>

                        {/* Action Buttons */}
                        <div className="flex gap-4">
                            <Button
                                variant="outline"
                                onClick={() => router.visit('/exam/form')}
                                className="flex-1"
                            >
                                <ArrowLeft className="mr-2 w-4 h-4" />
                                Go Back
                            </Button>
                            <Button
                                onClick={handleStartExam}
                                disabled={!accepted}
                                className="flex-1 glow-primary hover:scale-[1.02] transition-all"
                            >
                                Start Exam
                                <ArrowRight className="ml-2 w-4 h-4" />
                            </Button>
                        </div>
                    </Card>
                </div>
                </div>
            </div>
        </>
    );
}
