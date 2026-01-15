import { Head, router } from '@inertiajs/react';
import { useState, useEffect, useRef, useCallback } from 'react';
import { AlertTriangle, ArrowRight, CheckCircle2, Ban, Shield, Monitor, Calendar } from 'lucide-react';
import { Card } from '../components/ui/Card';
import { Button } from '../components/ui/Button';
import { ProgressBar } from '../components/ProgressBar';
import { ExamTimer } from '../components/ExamTimer';
import { MathTextRenderer } from '../components/MathTextRenderer';
import api from '../lib/api';

interface Option {
    id: number;
    text: string;
}

interface Question {
    id: number;
    text: string;
    category: string;
    options: Option[];
}

interface Progress {
    current: number;
    total: number;
    answered: number;
}

const EXAM_DURATION_MINUTES = 10;
const INACTIVITY_TIMEOUT_SECONDS = 30;

export default function ExamScreen({ token }: { token: string }) {
    const [question, setQuestion] = useState<Question | null>(null);
    const [progress, setProgress] = useState<Progress | null>(null);
    const [selectedOption, setSelectedOption] = useState<number | null>(null);
    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [error, setError] = useState('');
    const [showWarning, setShowWarning] = useState(false);
    const [warningType, setWarningType] = useState<'tab-switch' | 'dev-tools' | 'screenshot' | 'screen-share' | 'inactivity'>('tab-switch');
    const [startTime] = useState<Date>(new Date());
    const [violationCount, setViolationCount] = useState(0);
    const inactivityTimerRef = useRef<NodeJS.Timeout | null>(null);
    const devToolsCheckIntervalRef = useRef<NodeJS.Timeout | null>(null);
    const lastActivityRef = useRef<Date>(new Date());
    const tabSwitchCountRef = useRef(0);

    useEffect(() => {
        loadQuestion();
        
        // Disable text selection
        document.body.style.userSelect = 'none';
        document.body.style.webkitUserSelect = 'none';
        
        // Disable drag and drop
        document.addEventListener('dragstart', (e) => e.preventDefault());
        document.addEventListener('drop', (e) => e.preventDefault());
        
        return () => {
            document.body.style.userSelect = '';
            document.body.style.webkitUserSelect = '';
            if (inactivityTimerRef.current) {
                clearTimeout(inactivityTimerRef.current);
            }
            if (devToolsCheckIntervalRef.current) {
                clearInterval(devToolsCheckIntervalRef.current);
            }
        };
    }, []);

    // Anti-cheat: Detect dev tools
    useEffect(() => {
        const checkDevTools = () => {
            const widthThreshold = 200;
            const heightThreshold = 200;
            
            // Check if window size suggests dev tools are open
            if (
                window.outerWidth - window.innerWidth > widthThreshold ||
                window.outerHeight - window.innerHeight > heightThreshold
            ) {
                setWarningType('dev-tools');
                setShowWarning(true);
                setViolationCount(prev => prev + 1);
            }
        };

        // Check periodically
        devToolsCheckIntervalRef.current = setInterval(checkDevTools, 1000);

        // Check on resize
        window.addEventListener('resize', checkDevTools);

        return () => {
            if (devToolsCheckIntervalRef.current) {
                clearInterval(devToolsCheckIntervalRef.current);
            }
            window.removeEventListener('resize', checkDevTools);
        };
    }, []);

    // Anti-cheat: Detect keyboard shortcuts (F12, Ctrl+Shift+I, Ctrl+Shift+C, etc.)
    useEffect(() => {
        const handleKeyDown = (e: KeyboardEvent) => {
            // Disable F12
            if (e.key === 'F12') {
                e.preventDefault();
                setWarningType('dev-tools');
                setShowWarning(true);
                setViolationCount(prev => prev + 1);
                return false;
            }

            // Disable Ctrl+Shift+I (DevTools)
            if (e.ctrlKey && e.shiftKey && e.key === 'I') {
                e.preventDefault();
                setWarningType('dev-tools');
                setShowWarning(true);
                setViolationCount(prev => prev + 1);
                return false;
            }

            // Disable Ctrl+Shift+C (Inspect Element)
            if (e.ctrlKey && e.shiftKey && e.key === 'C') {
                e.preventDefault();
                setWarningType('dev-tools');
                setShowWarning(true);
                setViolationCount(prev => prev + 1);
                return false;
            }

            // Disable Ctrl+Shift+J (Console)
            if (e.ctrlKey && e.shiftKey && e.key === 'J') {
                e.preventDefault();
                setWarningType('dev-tools');
                setShowWarning(true);
                setViolationCount(prev => prev + 1);
                return false;
            }

            // Disable Ctrl+U (View Source)
            if (e.ctrlKey && e.key === 'u') {
                e.preventDefault();
                return false;
            }

            // Disable Ctrl+S (Save)
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                return false;
            }

            // Disable Ctrl+P (Print)
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                return false;
            }

            // Disable Ctrl+C and Ctrl+V (Copy/Paste)
            if (e.ctrlKey && (e.key === 'c' || e.key === 'v' || e.key === 'x')) {
                e.preventDefault();
                return false;
            }

            // Update activity on any key press
            lastActivityRef.current = new Date();
            resetInactivityTimer();
        };

        document.addEventListener('keydown', handleKeyDown);
        return () => document.removeEventListener('keydown', handleKeyDown);
    }, []);

    // Anti-cheat: Detect visibility change (tab switch)
    useEffect(() => {
        const handleVisibilityChange = () => {
            if (document.hidden) {
                tabSwitchCountRef.current += 1;
                setWarningType('tab-switch');
                setShowWarning(true);
                setViolationCount(prev => prev + 1);
            }
        };

        document.addEventListener('visibilitychange', handleVisibilityChange);
        return () => document.removeEventListener('visibilitychange', handleVisibilityChange);
    }, []);

    // Anti-cheat: Detect screen sharing
    useEffect(() => {
        const checkScreenShare = async () => {
            try {
                // This will throw an error if screen sharing is attempted
                // We can't directly detect it, but we can prevent it by warning
                // In a real implementation, you'd use getDisplayMedia API monitoring
            } catch (err) {
                // Handle screen sharing detection
            }
        };
    }, []);

    // Anti-cheat: Prevent context menu
    useEffect(() => {
        const handleContextMenu = (e: MouseEvent) => {
            e.preventDefault();
            setWarningType('dev-tools');
            setShowWarning(true);
            setViolationCount(prev => prev + 1);
        };

        document.addEventListener('contextmenu', handleContextMenu);
        return () => document.removeEventListener('contextmenu', handleContextMenu);
    }, []);

    // Anti-cheat: Detect screenshot attempts (clipboard monitoring)
    useEffect(() => {
        const handlePaste = (e: ClipboardEvent) => {
            e.preventDefault();
        };

        const handleCopy = (e: ClipboardEvent) => {
            e.preventDefault();
        };

        document.addEventListener('paste', handlePaste);
        document.addEventListener('copy', handleCopy);
        return () => {
            document.removeEventListener('paste', handlePaste);
            document.removeEventListener('copy', handleCopy);
        };
    }, []);

    // Track mouse activity for inactivity detection
    useEffect(() => {
        const handleActivity = () => {
            lastActivityRef.current = new Date();
            resetInactivityTimer();
        };

        document.addEventListener('mousemove', handleActivity);
        document.addEventListener('mousedown', handleActivity);
        document.addEventListener('keypress', handleActivity);
        document.addEventListener('scroll', handleActivity);

        return () => {
            document.removeEventListener('mousemove', handleActivity);
            document.removeEventListener('mousedown', handleActivity);
            document.removeEventListener('keypress', handleActivity);
            document.removeEventListener('scroll', handleActivity);
        };
    }, []);

    // Inactivity detection - show warning after 30 seconds (but don't skip)
    const resetInactivityTimer = useCallback(() => {
        if (inactivityTimerRef.current) {
            clearTimeout(inactivityTimerRef.current);
        }

        inactivityTimerRef.current = setTimeout(() => {
            // Show warning for inactivity (but don't skip question)
            setWarningType('inactivity');
            setShowWarning(true);
        }, INACTIVITY_TIMEOUT_SECONDS * 1000);
    }, []);

    // Start inactivity timer when question loads
    useEffect(() => {
        if (question && !loading) {
            resetInactivityTimer();
        }
        return () => {
            if (inactivityTimerRef.current) {
                clearTimeout(inactivityTimerRef.current);
            }
        };
    }, [question, loading, resetInactivityTimer]);

    const loadQuestion = async () => {
        try {
            setLoading(true);
            if (inactivityTimerRef.current) {
                clearTimeout(inactivityTimerRef.current);
            }
            const response = await api.get(`/session/${token}/question`);
            setQuestion(response.data.question);
            setProgress(response.data.progress);
            setSelectedOption(null);
            setError('');
            setSubmitting(false);
            lastActivityRef.current = new Date();
            resetInactivityTimer();
        } catch (err: any) {
            if (err.response?.status === 404) {
                // No more questions, finish exam
                handleFinish();
            } else {
                setError(err.response?.data?.error || 'Failed to load question');
            }
            setSubmitting(false);
        } finally {
            setLoading(false);
        }
    };

    const handleSubmit = async () => {
        if (!selectedOption || !question) return;

        try {
            setSubmitting(true);
            if (inactivityTimerRef.current) {
                clearTimeout(inactivityTimerRef.current);
            }
            await api.post(`/session/${token}/answer`, {
                question_id: question.id,
                option_id: selectedOption,
            });
            // Move to next question
            loadQuestion();
        } catch (err: any) {
            setError(err.response?.data?.error || 'Failed to submit answer');
            setSubmitting(false);
        }
    };

    const handleFinish = useCallback(async () => {
        if (inactivityTimerRef.current) {
            clearTimeout(inactivityTimerRef.current);
        }

        if (!question) {
            // No current question, just finish
            try {
                await api.post(`/session/${token}/finish`);
                router.visit(`/exam/${token}/complete`);
            } catch (err: any) {
                setError(err.response?.data?.error || 'Failed to finish exam');
            }
            return;
        }

        if (selectedOption) {
            // Submit current answer first
            try {
                await api.post(`/session/${token}/answer`, {
                    question_id: question.id,
                    option_id: selectedOption,
                });
            } catch (err: any) {
                // Continue even if answer submission fails
            }
        }

        try {
            setSubmitting(true);
            await api.post(`/session/${token}/finish`);
            router.visit(`/exam/${token}/complete`);
        } catch (err: any) {
            setError(err.response?.data?.error || 'Failed to finish exam');
            setSubmitting(false);
        }
    }, [token, question, selectedOption]);

    const handleTimeUp = () => {
        handleFinish();
    };

    // Auto-complete exam after 3 violations
    useEffect(() => {
        if (violationCount >= 3) {
            handleFinish();
        }
    }, [violationCount, handleFinish]);

    const getWarningMessage = () => {
        switch (warningType) {
            case 'tab-switch':
                return {
                    title: 'Tab Switch Detected!',
                    message: 'Switching tabs or leaving the exam page is prohibited. Your exam may be terminated if this continues.',
                };
            case 'dev-tools':
                return {
                    title: 'Developer Tools Detected!',
                    message: 'Opening developer tools or inspecting elements is strictly prohibited. This violation may result in exam termination.',
                };
            case 'screenshot':
                return {
                    title: 'Screenshot Attempt Detected!',
                    message: 'Taking screenshots or sharing screen during the exam is prohibited.',
                };
            case 'screen-share':
                return {
                    title: 'Screen Sharing Detected!',
                    message: 'Screen sharing during the exam is strictly prohibited.',
                };
            case 'inactivity':
                return {
                    title: 'Inactivity Detected!',
                    message: `You were inactive for ${INACTIVITY_TIMEOUT_SECONDS} seconds. Please stay active during the exam.`,
                };
            default:
                return {
                    title: 'Warning!',
                    message: 'A violation has been detected. Please follow the exam rules.',
                };
        }
    };

    if (loading) {
        return (
            <div className="min-h-screen gradient-bg flex items-center justify-center">
                <div className="text-center">
                    <div className="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin mx-auto mb-4" />
                    <p className="text-muted-foreground">Loading exam...</p>
                </div>
            </div>
        );
    }

    if (!question || !progress) {
        return (
            <div className="min-h-screen gradient-bg flex items-center justify-center">
                <div className="text-center">
                    <p className="text-destructive mb-4">{error || 'No question available'}</p>
                    <Button onClick={() => router.visit('/')}>Go Home</Button>
                </div>
            </div>
        );
    }

    const isLastQuestion = progress.current === progress.total;
    const warningMessage = getWarningMessage();

    return (
        <>
            <Head title="Exam - UGV Quiz" />
            <div className="min-h-screen gradient-bg">
                {/* Subtle UGV Branding Bar */}
                <div className="bg-card/50 backdrop-blur-sm border-b border-border/50">
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

                {/* Warning Modal */}
                {showWarning && (
                    <div className="fixed inset-0 bg-background/90 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                        <Card className="max-w-md w-full p-6 text-center card-shadow border-2 border-warning">
                            {warningType === 'dev-tools' && <Ban className="w-16 h-16 text-destructive mx-auto mb-4" />}
                            {warningType === 'tab-switch' && <Monitor className="w-16 h-16 text-warning mx-auto mb-4" />}
                            {warningType === 'screenshot' && <Shield className="w-16 h-16 text-warning mx-auto mb-4" />}
                            {warningType === 'screen-share' && <Shield className="w-16 h-16 text-warning mx-auto mb-4" />}
                            {warningType === 'inactivity' && <AlertTriangle className="w-16 h-16 text-warning mx-auto mb-4" />}
                            <h2 className="text-xl font-bold mb-2">{warningMessage.title}</h2>
                            <p className="text-muted-foreground mb-4">{warningMessage.message}</p>
                            {violationCount >= 3 && (
                                <p className="text-destructive font-semibold mb-4">
                                    Multiple violations detected! Your exam may be terminated.
                                </p>
                            )}
                            <Button
                                onClick={() => {
                                    setShowWarning(false);
                                    if (warningType !== 'inactivity') {
                                        resetInactivityTimer();
                                    }
                                }}
                                className="w-full"
                                variant={violationCount >= 3 ? 'destructive' : 'default'}
                            >
                                {warningType === 'inactivity' ? 'Continue Exam' : 'I Understand, Continue Exam'}
                            </Button>
                        </Card>
                    </div>
                )}

                {/* Header */}
                <div className="sticky top-0 bg-background/80 backdrop-blur-md border-b border-border z-40">
                    <div className="container mx-auto px-4 py-3">
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-4">
                                <span className="font-semibold text-primary">UGV Quiz</span>
                                <span className="text-sm text-muted-foreground hidden sm:inline">
                                    Question {progress.current} of {progress.total}
                                </span>
                            </div>
                            <ExamTimer startTime={startTime} durationMinutes={EXAM_DURATION_MINUTES} onTimeUp={handleTimeUp} />
                        </div>
                        <div className="mt-3">
                            <ProgressBar current={progress.current} total={progress.total} showLabel={false} />
                        </div>
                    </div>
                </div>

                {/* Question Area */}
                <div className="container mx-auto px-4 py-8 max-w-3xl">
                    <Card className="p-6 md:p-8 card-shadow border-0 animate-scale-in">
                        {/* Question Number Badge */}
                        <div className="inline-flex items-center gap-2 bg-primary/10 px-3 py-1 rounded-full mb-4">
                            <span className="text-sm font-medium text-primary">
                                Question {progress.current}
                            </span>
                        </div>

                        {/* Category Badge */}
                        <div className="mb-4">
                            <span className="inline-block px-3 py-1 bg-accent/10 text-accent rounded-full text-sm font-medium">
                                {question.category}
                            </span>
                        </div>

                        {/* Question Text */}
                        <h2 className="text-xl md:text-2xl font-semibold mb-8 leading-relaxed">
                            <MathTextRenderer content={question.text} display={false} />
                        </h2>

                        {error && (
                            <div className="mb-4 p-4 bg-destructive/10 border border-destructive/20 text-destructive rounded-lg text-sm">
                                {error}
                            </div>
                        )}

                        {/* Options */}
                        <div className="space-y-3">
                            {question.options.map((option, index) => (
                                <button
                                    key={option.id}
                                    onClick={() => {
                                        setSelectedOption(option.id);
                                        lastActivityRef.current = new Date();
                                        resetInactivityTimer();
                                    }}
                                    className={`w-full p-4 md:p-5 rounded-xl text-left transition-all duration-200 border-2 ${
                                        selectedOption === option.id
                                            ? 'border-primary bg-primary/10 glow-primary'
                                            : 'border-border bg-card hover:border-primary/50 hover:bg-primary/5'
                                    }`}
                                >
                                    <div className="flex items-center gap-4">
                                        <span
                                            className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold ${
                                                selectedOption === option.id
                                                    ? 'bg-primary text-primary-foreground'
                                                    : 'bg-secondary text-secondary-foreground'
                                            }`}
                                        >
                                            {String.fromCharCode(65 + index)}
                                        </span>
                                        <span className="flex-1 font-medium">
                                            <MathTextRenderer content={option.text} display={false} />
                                        </span>
                                        {selectedOption === option.id && (
                                            <CheckCircle2 className="w-5 h-5 text-primary" />
                                        )}
                                    </div>
                                </button>
                            ))}
                        </div>

                        {/* Next Button */}
                        <div className="mt-8 flex justify-end">
                            <Button
                                size="lg"
                                onClick={isLastQuestion ? handleFinish : handleSubmit}
                                disabled={selectedOption === null || submitting}
                                className="px-8 rounded-xl glow-primary hover:scale-[1.02] transition-all"
                            >
                                {submitting
                                    ? 'Submitting...'
                                    : isLastQuestion
                                      ? 'Submit Exam'
                                      : 'Next Question'}
                                <ArrowRight className="ml-2 w-5 h-5" />
                            </Button>
                        </div>
                    </Card>
                </div>
            </div>
        </>
    );
}
