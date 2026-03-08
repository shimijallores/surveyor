export type SurveyStatus = 'draft' | 'published' | 'closed';

export type SurveyQuestionType =
    | 'open_ended'
    | 'yes_no'
    | 'multiple_choice'
    | 'rating_scale'
    | 'ranking';

export type SurveyOption = {
    id: number | null;
    label: string;
    position: number;
};

export type SurveyQuestionSettings = {
    allow_multiple?: boolean;
    min?: number;
    max?: number;
    min_label?: string | null;
    max_label?: string | null;
};

export type SurveyQuestion = {
    id: number | null;
    type: SurveyQuestionType;
    title: string;
    description: string | null;
    is_required: boolean;
    position: number;
    settings: SurveyQuestionSettings;
    options: SurveyOption[];
};

export type SurveyBuilder = {
    id: number | null;
    public_id: string | null;
    title: string;
    description: string | null;
    status: SurveyStatus;
    published_at: string | null;
    closed_at: string | null;
    share_path: string | null;
    questions: SurveyQuestion[];
};

export type SurveySummary = {
    id: number;
    public_id: string;
    title: string;
    description: string | null;
    status: SurveyStatus;
    share_path: string;
    question_count: number;
    response_count: number;
    completed_count: number;
    published_at: string | null;
    closed_at: string | null;
    updated_at: string | null;
};

export type SurveyDashboardStats = {
    total_surveys: number;
    published_surveys: number;
    draft_surveys: number;
    closed_surveys: number;
    total_responses: number;
};

export type SurveyQuestionTypeOption = {
    value: SurveyQuestionType;
    label: string;
    description: string;
};

export type SurveyAnalyticsSegment = {
    label: string;
    count?: number;
    score?: number;
};

export type SurveyAnalyticsQuestion = {
    question_id: number;
    type: SurveyQuestionType;
    title: string;
    description: string | null;
    responses?: string[];
    segments?: SurveyAnalyticsSegment[];
    average?: number | null;
    scale?: {
        min: number;
        max: number;
        min_label?: string | null;
        max_label?: string | null;
    };
};

export type SurveyAnalytics = {
    summary: {
        response_count: number;
        completed_count: number;
        question_count: number;
        completion_rate: number;
    };
    questions: SurveyAnalyticsQuestion[];
};

export type PublicSurvey = {
    id: number;
    public_id: string;
    title: string;
    description: string | null;
    status: SurveyStatus;
    questions: SurveyQuestion[];
};

export type SurveyBuilderForm = {
    title: string;
    description: string;
    access_code: string;
    questions: SurveyQuestion[];
};

export type SurveyAnswers = Record<
    string,
    string | number | boolean | number[] | null
>;
