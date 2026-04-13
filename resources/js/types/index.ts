import { LucideIcon } from 'lucide-react';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    url: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    assistantCatalog?: AssistantCategory[];
    assistantFormula?: AssistantFormula;
    [key: string]: unknown;
}

export interface AssistantCommand {
    id: string;
    command: string;
    description: string;
    placeholders: string[];
}

export interface AssistantCategory {
    id: string;
    title: string;
    commands: AssistantCommand[];
}

export interface AssistantFormula {
    name: string;
    example_short: string;
    example_better: string;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}
