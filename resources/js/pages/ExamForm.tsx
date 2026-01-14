import { Head, router } from '@inertiajs/react';
import { useState, FormEvent } from 'react';
import { ArrowRight, User, Phone, GraduationCap, BookOpen, Building, Sparkles } from 'lucide-react';
import { Button } from '../components/ui/Button';
import { Card } from '../components/ui/Card';
import { Input } from '../components/ui/Input';
import { Label } from '../components/ui/Label';
import { ProgressBar } from '../components/ProgressBar';
import api from '../lib/api';

const boards = [
    'Dhaka',
    'Rajshahi',
    'Jessore',
    'Comilla',
    'Chittagong',
    'Barishal',
    'Sylhet',
    'Dinajpur',
    'Mymensingh',
    'Technical',
    'Madrasah',
];

export default function ExamForm() {
    const [formData, setFormData] = useState({
        exam_id: '1',
        full_name: '',
        phone: '',
        group: '',
        hsc_roll: '',
        board: '',
        college: '',
    });
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    const validateMobile = (mobile: string): boolean => {
        const mobileRegex = /^01[3-9]\d{8}$/;
        return mobileRegex.test(mobile);
    };

    const handleSubmit = async (e: FormEvent) => {
        e.preventDefault();
        setError('');

        // Validation
        if (!formData.full_name.trim()) {
            setError('Please enter your full name');
            return;
        }

        if (!validateMobile(formData.phone)) {
            setError('Please enter a valid Bangladeshi mobile number (e.g., 01712345678)');
            return;
        }

        if (!formData.group) {
            setError('Please select your group');
            return;
        }

        if (!formData.hsc_roll.trim()) {
            setError('Please enter your HSC roll number');
            return;
        }

        if (!formData.board) {
            setError('Please select your board');
            return;
        }

        if (!formData.college.trim()) {
            setError('Please enter your college name');
            return;
        }

        setLoading(true);

        try {
            const response = await api.post('/start', formData);
            router.visit(`/exam/${response.data.token}/rules`);
        } catch (err: any) {
            if (err.response?.status === 409 && err.response?.data?.token) {
                // Already participated
                if (err.response?.data?.completed) {
                    // Exam already completed, redirect to leaderboard
                    router.visit('/leaderboard');
                } else {
                    // Exam in progress, redirect to exam
                    router.visit(`/exam/${err.response.data.token}`);
                }
            } else {
                setError(err.response?.data?.error || 'Failed to start exam');
                setLoading(false);
            }
        }
    };

    return (
        <>
            <Head title="Student Registration - MeritSpark" />
            <div className="min-h-screen gradient-bg flex items-center justify-center p-4">
                <div className="w-full max-w-lg">
                    {/* Header */}
                    <div className="text-center mb-8">
                        <div className="inline-flex items-center gap-2 bg-primary/10 px-4 py-2 rounded-full mb-4">
                            <Sparkles className="w-4 h-4 text-primary" />
                            <span className="font-semibold text-primary">MeritSpark</span>
                        </div>
                        <h1 className="text-2xl md:text-3xl font-bold mb-2">Student Registration</h1>
                        <p className="text-muted-foreground">Fill in your details to participate</p>
                    </div>

                    {/* Progress */}
                    <div className="mb-6">
                        <ProgressBar current={1} total={2} showLabel={false} />
                        <p className="text-center text-sm text-muted-foreground mt-2">Step 1 of 2</p>
                    </div>

                    {/* Form Card */}
                    <Card className="p-6 md:p-8 card-shadow border-0">
                        {error && (
                            <div className="mb-4 p-4 bg-destructive/10 border border-destructive/20 text-destructive rounded-lg text-sm">
                                {error}
                            </div>
                        )}
                        <form onSubmit={handleSubmit} className="space-y-5">
                            {/* Full Name */}
                            <div className="space-y-2">
                                <Label htmlFor="fullName" className="flex items-center gap-2">
                                    <User className="w-4 h-4 text-muted-foreground" />
                                    Full Name
                                </Label>
                                <Input
                                    id="fullName"
                                    placeholder="Enter your full name"
                                    value={formData.full_name}
                                    onChange={(e) => setFormData({ ...formData, full_name: e.target.value })}
                                    className="h-12"
                                    required
                                />
                            </div>

                            {/* Mobile Number */}
                            <div className="space-y-2">
                                <Label htmlFor="mobile" className="flex items-center gap-2">
                                    <Phone className="w-4 h-4 text-muted-foreground" />
                                    Mobile Number
                                </Label>
                                <Input
                                    id="mobile"
                                    type="tel"
                                    placeholder="01XXXXXXXXX"
                                    value={formData.phone}
                                    onChange={(e) => setFormData({ ...formData, phone: e.target.value.replace(/\D/g, '').slice(0, 11) })}
                                    className="h-12"
                                    required
                                />
                            </div>

                            {/* Group */}
                            <div className="space-y-2">
                                <Label className="flex items-center gap-2">
                                    <BookOpen className="w-4 h-4 text-muted-foreground" />
                                    Group
                                </Label>
                                <select
                                    value={formData.group}
                                    onChange={(e) => setFormData({ ...formData, group: e.target.value })}
                                    className="flex h-12 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    required
                                >
                                    <option value="">Select your group</option>
                                    <option value="Science">Science</option>
                                    <option value="Arts">Arts (Humanities)</option>
                                    <option value="Commerce">Commerce (Business Studies)</option>
                                </select>
                            </div>

                            {/* HSC Roll */}
                            <div className="space-y-2">
                                <Label htmlFor="hscRoll" className="flex items-center gap-2">
                                    <GraduationCap className="w-4 h-4 text-muted-foreground" />
                                    HSC Roll Number
                                </Label>
                                <Input
                                    id="hscRoll"
                                    placeholder="Enter your HSC roll"
                                    value={formData.hsc_roll}
                                    onChange={(e) => setFormData({ ...formData, hsc_roll: e.target.value })}
                                    className="h-12"
                                    required
                                />
                            </div>

                            {/* Board */}
                            <div className="space-y-2">
                                <Label className="flex items-center gap-2">
                                    <BookOpen className="w-4 h-4 text-muted-foreground" />
                                    Board
                                </Label>
                                <select
                                    value={formData.board}
                                    onChange={(e) => setFormData({ ...formData, board: e.target.value })}
                                    className="flex h-12 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    required
                                >
                                    <option value="">Select your board</option>
                                    {boards.map((board) => (
                                        <option key={board} value={board}>
                                            {board} Board
                                        </option>
                                    ))}
                                </select>
                            </div>

                            {/* College Name */}
                            <div className="space-y-2">
                                <Label htmlFor="collegeName" className="flex items-center gap-2">
                                    <Building className="w-4 h-4 text-muted-foreground" />
                                    College Name
                                </Label>
                                <Input
                                    id="collegeName"
                                    placeholder="Enter your college name"
                                    value={formData.college}
                                    onChange={(e) => setFormData({ ...formData, college: e.target.value })}
                                    className="h-12"
                                    required
                                />
                            </div>

                            {/* Submit Button */}
                            <Button
                                type="submit"
                                size="lg"
                                className="w-full h-14 text-lg rounded-xl glow-primary hover:scale-[1.02] transition-all"
                                disabled={loading}
                            >
                                {loading ? 'Processing...' : 'Proceed to Exam'}
                                <ArrowRight className="ml-2 w-5 h-5" />
                            </Button>
                        </form>
                    </Card>

                    {/* Back Link */}
                    <div className="text-center mt-6">
                        <Button variant="ghost" onClick={() => router.visit('/')}>
                            ← Back to Home
                        </Button>
                    </div>
                </div>
            </div>
        </>
    );
}
