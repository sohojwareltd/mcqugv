import { Head, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { Trophy, Lock, Search, Medal, Sparkles, ArrowLeft } from 'lucide-react';
import { Card } from '../components/ui/Card';
import { Button } from '../components/ui/Button';
import { Input } from '../components/ui/Input';
import { CountdownTimer } from '../components/CountdownTimer';
import api from '../lib/api';

interface Participant {
    rank: number;
    full_name: string;
    phone: string;
    score: number;
    completed_at: string;
}

interface LeaderboardData {
    exam: {
        id: number;
        title: string;
    };
    participants: Participant[];
}

export default function Leaderboard({ examId }: { examId?: number }) {
    const [data, setData] = useState<LeaderboardData | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [searchTerm, setSearchTerm] = useState('');
    const [publishAt, setPublishAt] = useState<Date | null>(null);
    const [isUnlocked, setIsUnlocked] = useState(false);

    useEffect(() => {
        loadLeaderboard();
    }, [examId]);

    useEffect(() => {
        if (publishAt) {
            const checkUnlock = () => {
                const now = new Date();
                if (now >= publishAt) {
                    setIsUnlocked(true);
                    loadLeaderboard();
                }
            };

            checkUnlock();
            const interval = setInterval(checkUnlock, 1000);
            return () => clearInterval(interval);
        }
    }, [publishAt]);

    const loadLeaderboard = async () => {
        try {
            setLoading(true);
            const examIdToUse = examId || 1;
            const response = await api.get(`/exams/${examIdToUse}/leaderboard`);
            setData(response.data);
            setError('');
            setIsUnlocked(true);
        } catch (err: any) {
            if (err.response?.status === 403) {
                setError(err.response.data.error);
                if (err.response.data.publish_at) {
                    setPublishAt(new Date(err.response.data.publish_at));
                    setIsUnlocked(false);
                }
            } else {
                setError(err.response?.data?.error || 'Failed to load leaderboard');
            }
        } finally {
            setLoading(false);
        }
    };

    const getRankBadge = (rank: number) => {
        if (rank === 1) return <Medal className="w-6 h-6 text-yellow-500" />;
        if (rank === 2) return <Medal className="w-6 h-6 text-gray-400" />;
        if (rank === 3) return <Medal className="w-6 h-6 text-amber-600" />;
        return (
            <span className="w-6 h-6 flex items-center justify-center text-muted-foreground font-bold">{rank}</span>
        );
    };

    const formatTime = (dateString: string): string => {
        const date = new Date(dateString);
        const hours = date.getHours();
        const minutes = date.getMinutes();
        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
    };

    if (loading) {
        return (
            <div className="min-h-screen gradient-bg flex items-center justify-center">
                <div className="text-center">
                    <div className="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin mx-auto mb-4" />
                    <p className="text-muted-foreground">Loading leaderboard...</p>
                </div>
            </div>
        );
    }

    if (!isUnlocked && publishAt) {
        return (
            <div className="min-h-screen gradient-bg flex items-center justify-center p-4">
                <div className="max-w-md w-full text-center">
                    {/* Logo */}
                    <div className="inline-flex items-center gap-2 bg-primary/10 px-4 py-2 rounded-full mb-6">
                        <Sparkles className="w-4 h-4 text-primary" />
                        <span className="font-semibold text-primary">MeritSpark</span>
                    </div>

                    {/* Lock Icon */}
                    <div className="w-24 h-24 bg-secondary rounded-full flex items-center justify-center mx-auto mb-8 animate-float">
                        <Lock className="w-12 h-12 text-muted-foreground" />
                    </div>

                    {/* Title */}
                    <h1 className="text-3xl font-bold mb-4">Merit List Locked</h1>
                    <p className="text-muted-foreground mb-8">
                        The merit list will be unlocked at{' '}
                        <span className="font-semibold text-foreground">
                            {publishAt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}
                        </span>
                    </p>

                    {/* Countdown */}
                    <div className="flex justify-center mb-8">
                        <CountdownTimer
                            targetDate={publishAt}
                            onComplete={() => {
                                setIsUnlocked(true);
                                loadLeaderboard();
                            }}
                        />
                    </div>

                    {/* Back Button */}
                    <Button variant="outline" onClick={() => router.visit('/')}>
                        <ArrowLeft className="mr-2 w-4 h-4" />
                        Back to Home
                    </Button>
                </div>
            </div>
        );
    }

    if (!data || !data.participants.length) {
        return (
            <div className="min-h-screen gradient-bg flex items-center justify-center p-4">
                <div className="text-center">
                    <p className="text-muted-foreground mb-4">{error || 'No participants found'}</p>
                    <Button onClick={() => router.visit('/')}>Go Home</Button>
                </div>
            </div>
        );
    }

    const filteredList = data.participants.filter(
        (p) =>
            p.full_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
            p.phone.includes(searchTerm)
    );

    return (
        <>
            <Head title="Merit List - MeritSpark" />
            <div className="min-h-screen gradient-bg">
                {/* Header */}
                <div className="bg-primary text-primary-foreground py-12">
                    <div className="container mx-auto px-4 text-center">
                        <div className="inline-flex items-center gap-2 bg-primary-foreground/10 px-4 py-2 rounded-full mb-4">
                            <Trophy className="w-5 h-5" />
                            <span className="font-semibold">Merit List 2024</span>
                        </div>
                        <h1 className="text-3xl md:text-4xl font-bold mb-2">Congratulations to All Participants!</h1>
                        <p className="text-primary-foreground/80">Total Participants: {data.participants.length}</p>
                    </div>
                </div>

                {/* Search & List */}
                <div className="container mx-auto px-4 py-8 max-w-4xl">
                    {/* Search Bar */}
                    <div className="relative mb-6">
                        <Search className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-muted-foreground" />
                        <Input
                            placeholder="Search by name or phone..."
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                            className="pl-12 h-12"
                        />
                    </div>

                    {/* Results Count */}
                    <p className="text-sm text-muted-foreground mb-4">
                        Showing {filteredList.length} of {data.participants.length} results
                    </p>

                    {/* Merit List */}
                    <div className="space-y-3">
                        {filteredList.length === 0 ? (
                            <Card className="p-8 text-center card-shadow border-0">
                                <p className="text-muted-foreground">No results found for "{searchTerm}"</p>
                            </Card>
                        ) : (
                            filteredList.map((participant) => (
                                <Card
                                    key={participant.rank}
                                    className={`p-4 card-shadow border-0 transition-all hover:scale-[1.01] ${
                                        participant.rank <= 3 ? 'ring-2 ring-primary/20' : ''
                                    }`}
                                >
                                    <div className="flex items-center gap-4">
                                        {/* Rank */}
                                        <div className="flex-shrink-0 w-12 h-12 rounded-full bg-secondary flex items-center justify-center">
                                            {getRankBadge(participant.rank)}
                                        </div>

                                        {/* Info */}
                                        <div className="flex-1 min-w-0">
                                            <h3 className="font-semibold truncate">{participant.full_name}</h3>
                                            <p className="text-sm text-muted-foreground truncate">{participant.phone}</p>
                                        </div>

                                        {/* Score & Time */}
                                        <div className="flex-shrink-0 text-right">
                                            <div className="text-lg font-bold text-primary">{participant.score}</div>
                                            <div className="text-xs text-muted-foreground">
                                                {formatTime(participant.completed_at)}
                                            </div>
                                        </div>
                                    </div>
                                </Card>
                            ))
                        )}
                    </div>

                    {/* Back Button */}
                    <div className="mt-8 text-center">
                        <Button variant="outline" onClick={() => router.visit('/')}>
                            <ArrowLeft className="mr-2 w-4 h-4" />
                            Back to Home
                        </Button>
                    </div>
                </div>
            </div>
        </>
    );
}
