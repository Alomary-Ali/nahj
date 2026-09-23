import { Config } from 'ziggy-js';

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
}

export interface QuizQuestionOption {
    id: number;
    question_id: number;
    option_text: string;
    sort_order: number;
    is_correct?: boolean;
}

export interface QuizQuestion {
    id: number;
    quiz_id: number;
    question_text: string;
    type: 'single_choice' | 'multiple_choice' | 'true_false';
    points: number;
    explanation?: string | null;
    sort_order: number;
    options: QuizQuestionOption[];
}

export interface QuizSubmission {
    id: number;
    quiz_id: number;
    user_id: number;
    score: number;
    total_points: number;
    percentage: number;
    passed: boolean;
    attempt_number: number;
    answers?: Record<string | number, any>;
    submitted_at: string;
}

export interface Quiz {
    id: number;
    course_id: number;
    lesson_id?: number | null;
    title: string;
    description?: string | null;
    passing_percentage: number;
    time_limit_minutes?: number | null;
    max_attempts: number;
    questions: QuizQuestion[];
}

export interface Certificate {
    id: number;
    certificate_code: string;
    user_id: number;
    course_id: number;
    instructor_id: number;
    grade_percentage: number;
    issue_date: string;
    verification_hash: string;
    verification_url?: string;
    user?: User;
    course?: {
        id: number;
        title: string;
        slug: string;
        headline?: string;
        duration_hours?: number;
    };
    instructor?: {
        id: number;
        name: string;
    };
}

export interface CourseSummary {
    id: number;
    title: string;
    slug: string;
    headline?: string;
    description?: string;
    thumbnail?: string;
    level?: string;
    duration_hours?: number;
    price?: number;
    instructor?: User;
}

export interface DiscussionReply {
    id: number;
    thread_id: number;
    user_id: number;
    body: string;
    upvotes_count: number;
    is_instructor_endorsed: boolean;
    created_at: string;
    user?: User;
}

export interface DiscussionThread {
    id: number;
    course_id: number;
    lesson_id?: number | null;
    user_id: number;
    title: string;
    body: string;
    upvotes_count: number;
    replies_count: number;
    is_pinned: boolean;
    is_resolved: boolean;
    created_at: string;
    user?: User;
    lesson?: {
        id: number;
        title: string;
        slug: string;
    };
    replies?: DiscussionReply[];
}

export interface CourseReview {
    id: number;
    course_id: number;
    user_id: number;
    rating: number;
    review_text?: string | null;
    is_published: boolean;
    created_at: string;
    user?: User;
}

export interface RatingStats {
    average: number;
    total_reviews: number;
    distribution: Record<number, { count: number; percentage: number }>;
}

export interface LessonNote {
    id: number;
    user_id: number;
    course_id: number;
    lesson_id: number;
    timestamp_seconds: number;
    note_text: string;
    created_at: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
        can_access_admin?: boolean;
    };
    locale?: string;
    direction?: 'rtl' | 'ltr';
    translations?: Record<string, string>;
    flash?: {
        success?: string | null;
        error?: string | null;
    };
    ziggy: Config & { location: string };
};
