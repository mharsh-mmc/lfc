import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface Flash {
    success?: string;
    error?: string;
    warning?: string;
    info?: string;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
    flash?: Flash;
};

export interface User {
    id: number;
    name: string;
    username?: string; // Optional since it might be null for existing users
    email: string;
    email_verified_at?: string;
    title?: string;
    date_of_birth?: string;
    location?: string;
    bio?: string;
    height_cm?: number;
    weight_kg?: number;
    passion?: string;
    profession?: string;
    mission?: string;
    calling?: string;
    about_content?: string;
    connections_count: number;
    tributes_count: number;
    flowers_count: number;
    is_public?: boolean;
    last_activity?: string;
    banner_path?: string;
    banner_url?: string;
    profile_photo_path?: string;
    profile_photo_url?: string;
    created_at: string;
    updated_at: string;
    is_public?: boolean;
    settings?: {
        subscription_plan: string;
        privacy_settings: {
            profile_visible: boolean;
            show_tributes: boolean;
            allow_tribute_requests: boolean;
            email_notifications: boolean;
        };
        permissions: {
            legacy_messages: boolean;
            family_management: boolean;
            ai_suggestions: boolean;
        };
    };
}

export interface Notification {
    id: number;
    user_id: number;
    type: string;
    title: string;
    message: string;
    data?: any;
    is_read: boolean;
    read_at?: string;
    created_at: string;
    updated_at: string;
    time_ago: string;
}

export type BreadcrumbItemType = BreadcrumbItem;
