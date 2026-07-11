import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem } from '@/types';
import { MessageSquare, Plus } from 'lucide-react';

interface Message {
    id: number;
    body: string;
    sender_id: number;
    created_at: string;
}

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
}

interface Conversation {
    id: number;
    admin_id: number;
    customer_id: number;
    subject: string | null;
    last_message_at: string | null;
    admin: User;
    customer: User;
    latest_message: Message | null;
}

interface Props {
    conversations: {
        data: Conversation[];
        links: any;
        meta: any;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Messages',
        href: '/conversations',
    },
];

export default function MessengerIndex({ conversations }: Props) {
    const formatTime = (date: string) => {
        const d = new Date(date);
        const now = new Date();
        const diff = now.getTime() - d.getTime();
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        
        if (days === 0) {
            return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        } else if (days === 1) {
            return 'Yesterday';
        } else if (days < 7) {
            return `${days}d ago`;
        } else {
            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Messages" />
            <div className="space-y-4">
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-2">
                        <MessageSquare className="h-6 w-6" />
                        <h1 className="text-2xl font-bold">Messages</h1>
                    </div>
                    <Link href={route('conversations.create')}>
                        <Button className="flex items-center gap-2">
                            <Plus className="h-4 w-4" />
                            New Conversation
                        </Button>
                    </Link>
                </div>

                {conversations.data.length === 0 ? (
                    <div className="rounded-lg border border-dashed border-gray-300 p-8 text-center">
                        <MessageSquare className="mx-auto h-12 w-12 text-gray-400 mb-3" />
                        <p className="text-gray-600">No conversations yet</p>
                        <p className="text-sm text-gray-500 mt-1">Start a new conversation with a customer</p>
                        <Link href={route('conversations.create')} className="mt-4 inline-block">
                            <Button>Start Conversation</Button>
                        </Link>
                    </div>
                ) : (
                    <div className="space-y-2">
                        {conversations.data.map((conversation) => (
                            <Link 
                                key={conversation.id} 
                                href={route('conversations.show', conversation.id)}
                                className="block"
                            >
                                <div className="rounded-lg border border-gray-200 p-4 hover:bg-gray-50 transition cursor-pointer">
                                    <div className="flex items-start justify-between">
                                        <div className="flex-1">
                                            <h3 className="font-semibold text-gray-900">
                                                {conversation.customer.name}
                                            </h3>
                                            <p className="text-sm text-gray-600 mt-1">
                                                {conversation.latest_message?.body || 'No messages yet'}
                                            </p>
                                            {conversation.subject && (
                                                <p className="text-xs text-gray-500 mt-1">
                                                    Subject: {conversation.subject}
                                                </p>
                                            )}
                                        </div>
                                        <div className="text-right">
                                            <p className="text-xs text-gray-500">
                                                {conversation.last_message_at ? formatTime(conversation.last_message_at) : 'No activity'}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </Link>
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
