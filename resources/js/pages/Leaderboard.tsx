import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { Trophy, Search, Medal, Sparkles, ArrowLeft, Clock, Calendar, Facebook } from 'lucide-react';
import { Card } from '../components/ui/Card';
import { Button } from '../components/ui/Button';
import { Input } from '../components/ui/Input';
import { CountdownTimer } from '../components/CountdownTimer';

interface Participant {
    rank: number;
    merit_position?: number;
    full_name: string;
    hsc_roll: string | null;
    score: number;
    completed_at: string;
}

interface PreviousExam {
    id: number;
    title: string;
    end_time?: string;
    result_publish_at?: string;
    participants: Participant[];
}

interface LeaderboardProps {
    currentExam?: {
        id: number;
        title: string;
        result_publish_at?: string;
    } | null;
    nextExam?: {
        id: number;
        title: string;
        start_time: string;
    } | null;
    previousExams: PreviousExam[];
}

export default function Leaderboard({ currentExam, nextExam, previousExams }: LeaderboardProps) {
    const [searchTerm, setSearchTerm] = useState('');
    const [activeTab, setActiveTab] = useState<'current' | 'previous'>(
        previousExams.length > 0 ? 'previous' : 'current'
    );
    const [selectedExamId, setSelectedExamId] = useState<number | null>(
        previousExams.length > 0 ? previousExams[0].id : null
    );

    const getRankBadge = (rank: number) => {
        if (rank === 1) return <Medal className="w-5 h-5 md:w-6 md:h-6 text-yellow-500" />;
        if (rank === 2) return <Medal className="w-5 h-5 md:w-6 md:h-6 text-gray-400" />;
        if (rank === 3) return <Medal className="w-5 h-5 md:w-6 md:h-6 text-amber-600" />;
        return (
            <span className="w-6 h-6 md:w-8 md:h-8 flex items-center justify-center text-muted-foreground font-bold text-sm md:text-base">
                {rank}
            </span>
        );
    };

    const formatTime = (dateString: string): string => {
        const date = new Date(dateString);
        const hours = date.getHours();
        const minutes = date.getMinutes();
        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
    };

    const formatDate = (dateString: string): string => {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    };

    // Get selected exam data
    const selectedExam = previousExams.find(exam => exam.id === selectedExamId);
    const allParticipants = selectedExam?.participants || [];

    // Filter participants by search term (name or HSC roll)
    const filteredList = allParticipants.filter(
        (p) => {
            const searchLower = searchTerm.toLowerCase();
            return (
                p.full_name.toLowerCase().includes(searchLower) ||
                (p.hsc_roll && p.hsc_roll.toLowerCase().includes(searchLower))
            );
        }
    );

    // Determine what timer to show
    const getTimerInfo = () => {
        if (nextExam && activeTab === 'current') {
            return {
                label: 'Next Exam Starts In',
                targetDate: new Date(nextExam.start_time),
            };
        }
        if (currentExam?.result_publish_at && activeTab === 'current') {
            const publishDate = new Date(currentExam.result_publish_at);
            if (publishDate > new Date()) {
                const formattedDate = publishDate.toLocaleDateString('en-US', { 
                    month: 'long', 
                    day: 'numeric', 
                    year: 'numeric' 
                });
                const formattedTime = publishDate.toLocaleTimeString('en-US', { 
                    hour: '2-digit', 
                    minute: '2-digit',
                    hour12: true 
                });
                return {
                    label: 'The results are published daily at 12:00 AM on our Facebook page.',
                    targetDate: publishDate,
                    publishDate: formattedDate,
                    publishTime: formattedTime,
                };
            }
        }
        return null;
    };

    const timerInfo = getTimerInfo();

    return (
        <>
            <Head title="Merit List - UGV Quiz" />
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

                {/* Header */}
                <div className="bg-primary text-primary-foreground py-8 md:py-12">
                    <div className="container mx-auto px-4 text-center">
                        <div className="mb-6">
                            <img 
                                src="/UGV-Logo-02.png" 
                                alt="University of Global Village" 
                                className="h-16 md:h-20 w-auto mx-auto object-contain "
                            />
                        </div>
                        <div className="inline-flex items-center gap-2 bg-primary-foreground/10 px-4 py-2 rounded-full mb-4">
                            <Trophy className="w-4 h-4 md:w-5 md:h-5" />
                            <span className="font-semibold text-sm md:text-base">UGV Quiz Merit List</span>
                        </div>
                        <h1 className="text-2xl md:text-3xl lg:text-4xl font-bold mb-2">
                            Congratulations to All Participants!
                        </h1>
                        {selectedExam && activeTab === 'previous' && (
                            <p className="text-primary-foreground/80 text-sm md:text-base">
                                {selectedExam.title} • {allParticipants.length} Participants
                            </p>
                        )}
                    </div>
                </div>

                {/* Tabs */}
                <div className="container mx-auto px-4 pt-6">
                    <div className="max-w-6xl mx-auto">
                        <div className="flex border-b border-border mb-6 overflow-x-auto">
                            <button
                                onClick={() => setActiveTab('current')}
                                className={`px-4 md:px-6 py-3 font-semibold text-sm md:text-base whitespace-nowrap border-b-2 transition-colors ${
                                    activeTab === 'current'
                                        ? 'border-primary text-primary'
                                        : 'border-transparent text-muted-foreground hover:text-foreground'
                                }`}
                            >
                                Current Status
                            </button>
                            {previousExams.length > 0 && (
                                <button
                                    onClick={() => setActiveTab('previous')}
                                    className={`px-4 md:px-6 py-3 font-semibold text-sm md:text-base whitespace-nowrap border-b-2 transition-colors ${
                                        activeTab === 'previous'
                                            ? 'border-primary text-primary'
                                            : 'border-transparent text-muted-foreground hover:text-foreground'
                                    }`}
                                >
                                    Previous Results ({previousExams.length})
                                </button>
                            )}
                        </div>
                    </div>
                </div>


                {/* Main Content */}
                <div className="container mx-auto px-4 py-6 md:py-8 max-w-6xl">
                    {activeTab === 'current' ? (
                        <Card className="p-6 md:p-8 card-shadow border-0 text-center">
                            {currentExam ? (
                                <>
                                    <h2 className="text-xl md:text-2xl font-bold mb-4">{currentExam.title}</h2>
                                    <p className="text-muted-foreground mb-6">
                                        Exam is currently running. Results will be published after the exam ends.
                                    </p>
                                    {timerInfo && (
                                        <div className="mt-6 flex justify-center">
                                            <div className="text-center max-w-2xl">
                                                {timerInfo.label && timerInfo.label.includes('Results will be published') && (timerInfo as any).publishDate ? (
                                                    <>
                                                        <p className="text-sm md:text-base font-semibold text-foreground mb-2">
                                                            Results will be published on the website at{' '}
                                                            <span className="text-primary">{(timerInfo as any).publishTime}</span> on{' '}
                                                            <span className="text-primary">{(timerInfo as any).publishDate}</span>
                                                        </p>
                                                        <p className="text-xs md:text-sm text-muted-foreground mb-1"> The results are published daily at 12:00 AM on our <a href="https://www.facebook.com/ugvbarisal" target="_blank" rel="noopener noreferrer" className="text-primary hover:underline font-semibold inline-flex items-center gap-1"><Facebook className="w-3 h-3" />Facebook page</a>.  </p>
                                                        <p className="text-sm md:text-base text-muted-foreground mb-3 mt-2">Countdown:</p>
                                                    </>
                                                ) : (
                                                    <p className="text-sm md:text-base text-muted-foreground mb-3">{timerInfo.label}</p>
                                                )}
                                                <CountdownTimer targetDate={timerInfo.targetDate} />
                                            </div>
                                        </div>
                                    )}
                                </>
                            ) : nextExam ? (
                                <>
                                    <h2 className="text-xl md:text-2xl font-bold mb-4">Next Exam: {nextExam.title}</h2>
                                    <p className="text-muted-foreground mb-6">Exam will start soon.</p>
                                    {timerInfo && (
                                        <div className="mt-6 flex justify-center">
                                            <div className="text-center">
                                                <p className="text-sm md:text-base text-muted-foreground mb-3">{timerInfo.label}</p>
                                                <CountdownTimer targetDate={timerInfo.targetDate} />
                                            </div>
                                        </div>
                                    )}
                                </>
                            ) : (
                                <p className="text-muted-foreground">No active exam at the moment.</p>
                            )}
                        </Card>
                    ) : (
                        <>
                            {/* Exam Selector - Mobile Friendly Tabs */}
                            {previousExams.length > 1 && (
                                <div className="mb-6">
                                    <div className="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
                                        {previousExams.map((exam) => (
                                            <button
                                                key={exam.id}
                                                onClick={() => {
                                                    setSelectedExamId(exam.id);
                                                    setSearchTerm('');
                                                }}
                                                className={`px-4 py-2 rounded-lg text-sm md:text-base font-medium whitespace-nowrap transition-colors ${
                                                    selectedExamId === exam.id
                                                        ? 'bg-primary text-primary-foreground'
                                                        : 'bg-secondary text-foreground hover:bg-secondary/80'
                                                }`}
                                            >
                                                {exam.title}
                                                {exam.end_time && (
                                                    <span className="ml-2 text-xs opacity-75">
                                                        ({formatDate(exam.end_time)})
                                                    </span>
                                                )}
                                            </button>
                                        ))}
                                    </div>
                                </div>
                            )}

                            {!selectedExam ? (
                                <Card className="p-6 md:p-8 card-shadow border-0 text-center">
                                    <p className="text-muted-foreground">No published results available yet.</p>
                                </Card>
                            ) : (
                                <>
                                    {/* Search Bar */}
                                    <div className="relative mb-4 md:mb-6">
                                        <Search className="absolute left-3 md:left-4 top-1/2 -translate-y-1/2 w-4 h-4 md:w-5 md:h-5 text-muted-foreground" />
                                        <Input
                                            placeholder="Search by name or HSC roll..."
                                            value={searchTerm}
                                            onChange={(e) => setSearchTerm(e.target.value)}
                                            className="pl-10 md:pl-12 h-11 md:h-12 text-sm md:text-base"
                                        />
                                    </div>

                                    {/* Results Count */}
                                    <p className="text-xs md:text-sm text-muted-foreground mb-4">
                                        Showing {filteredList.length} of {allParticipants.length} results
                                        {searchTerm && ` for "${searchTerm}"`}
                                    </p>

                                    {/* Merit List */}
                                    <div className="space-y-2 md:space-y-3">
                                        {filteredList.length === 0 ? (
                                            <Card className="p-6 md:p-8 text-center card-shadow border-0">
                                                <p className="text-muted-foreground">No results found for "{searchTerm}"</p>
                                            </Card>
                                        ) : (
                                            filteredList.map((participant) => (
                                                <Card
                                                    key={`${selectedExam.id}-${participant.rank}`}
                                                    className={`p-3 md:p-4 card-shadow border-0 transition-all hover:scale-[1.01] ${
                                                        participant.rank <= 3 ? 'ring-2 ring-primary/20' : ''
                                                    }`}
                                                >
                                                    <div className="flex items-center gap-3 md:gap-4">
                                                        {/* Rank */}
                                                        <div className="flex-shrink-0 w-10 h-10 md:w-12 md:h-12 rounded-full bg-secondary flex items-center justify-center">
                                                            {getRankBadge(participant.rank)}
                                                        </div>

                                                        {/* Info */}
                                                        <div className="flex-1 min-w-0">
                                                            <h3 className="font-semibold truncate text-sm md:text-base">
                                                                {participant.full_name}
                                                            </h3>
                                                            {participant.hsc_roll && (
                                                                <p className="text-xs md:text-sm text-muted-foreground truncate">
                                                                    HSC Roll: {participant.hsc_roll}
                                                                </p>
                                                            )}
                                                        </div>

                                                        {/* Score & Time */}
                                                        <div className="flex-shrink-0 text-right">
                                                            <div className="text-base md:text-lg font-bold text-primary">
                                                                {participant.score}
                                                            </div>
                                                            <div className="text-xs text-muted-foreground">
                                                                {formatTime(participant.completed_at)}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </Card>
                                            ))
                                        )}
                                    </div>
                                </>
                            )}
                        </>
                    )}

                    {/* Back Button */}
                    <div className="mt-6 md:mt-8 text-center">
                        <Button variant="outline" onClick={() => router.visit('/')} className="w-full md:w-auto">
                            <ArrowLeft className="mr-2 w-4 h-4" />
                            Back to Home
                        </Button>
                    </div>
                </div>
            </div>
        </>
    );
}
