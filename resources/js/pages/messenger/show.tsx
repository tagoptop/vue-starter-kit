import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { type BreadcrumbItem } from '@/types';
import { Send, FileUp, Trash2 } from 'lucide-react';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
}

interface Message {
    id: number;
    conversation_id: number;
    sender_id: number;
    body: string;
    file_path: string | null;
    file_name: string | null;
    created_at: string;
    sender: User;
}

interface Conversation {
    id: number;
    admin_id: number;
    customer_id: number;
    subject: string | null;
    created_at: string;
    admin: User;
    customer: User;
}

interface Props {
    conversation: Conversation;
    messages: Message[];
    otherUser: User;
    auth: { user: User };
}

export default function ConversationShow({ conversation, messages: initialMessages, otherUser, auth }: Props) {
    const [messages, setMessages] = useState<Message[]>(initialMessages);
    const [messageBody, setMessageBody] = useState('');
    const [selectedFile, setSelectedFile] = useState<File | null>(null);
    const [fileName, setFileName] = useState('');
    const [isLoading, setIsLoading] = useState(false);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Messages',
            href: '/conversations',
        },
        {
            title: otherUser.name,
            href: `/conversations/${conversation.id}`,
        },
    ];

    const handleFileSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            setSelectedFile(file);
            setFileName(file.name);
        }
    };

    const handleSendMessage = (e: React.FormEvent) => {
        e.preventDefault();
        
        if (!messageBody.trim() && !selectedFile) {
            return;
        }

        setIsLoading(true);

        const formData = new FormData();
        if (messageBody.trim()) {
            formData.append('body', messageBody);
        }
        if (selectedFile) {
            formData.append('file', selectedFile);
        }

        router.post(route('messages.store', conversation.id), formData, {
            onFinish: () => {
                setMessageBody('');
                setSelectedFile(null);
                setFileName('');
                setIsLoading(false);
                // Reload to get new messages
                window.location.reload();
            },
        });
    };

    const handleDeleteMessage = (messageId: number) => {
        if (confirm('Delete this message?')) {
            router.delete(route('messages.destroy', messageId), {
                onFinish: () => {
                    setMessages(messages.filter(m => m.id !== messageId));
                },
            });
        }
    };

    const formatMessageTime = (date: string) => {
        const d = new Date(date);
        return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    };

    const formatMessageDate = (date: string) => {
        const d = new Date(date);
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Conversation with ${otherUser.name}`} />
            <div className="flex flex-col h-screen">
                {/* Header */}
                <div className="border-b border-gray-200 bg-white p-4">
                    <h2 className="text-xl font-semibold">{otherUser.name}</h2>
                    {conversation.subject && (
                        <p className="text-sm text-gray-600">{conversation.subject}</p>
                    )}
                    <p className="text-xs text-gray-500 mt-1">{otherUser.email}</p>
                </div>

                {/* Messages */}
                <div className="flex-1 overflow-y-auto bg-gray-50 p-4 space-y-4">
                    {messages.length === 0 ? (
                        <div className="text-center text-gray-500 py-8">
                            <p>No messages yet. Start the conversation!</p>
                        </div>
                    ) : (
                        messages.map((message, index) => {
                            const showDate = index === 0 || 
                                formatMessageDate(messages[index - 1].created_at) !== formatMessageDate(message.created_at);
                            
                            const isOwnMessage = message.sender_id === auth.user.id;

                            return (
                                <div key={message.id}>
                                    {showDate && (
                                        <div className="flex justify-center mb-2">
                                            <span className="text-xs text-gray-500 bg-gray-200 px-3 py-1 rounded-full">
                                                {formatMessageDate(message.created_at)}
                                            </span>
                                        </div>
                                    )}
                                    <div className={`flex ${isOwnMessage ? 'justify-end' : 'justify-start'}`}>
                                        <div className={`max-w-xs lg:max-w-md px-4 py-2 rounded-lg ${
                                            isOwnMessage 
                                                ? 'bg-blue-600 text-white' 
                                                : 'bg-white text-gray-900 border border-gray-200'
                                        }`}>
                                            {message.body && (
                                                <p className="break-words">{message.body}</p>
                                            )}
                                            {message.file_path && (
                                                <a 
                                                    href={message.file_path} 
                                                    download
                                                    className={`inline-flex items-center gap-2 mt-2 underline text-sm ${
                                                        isOwnMessage ? 'text-blue-100' : 'text-blue-600'
                                                    }`}
                                                >
                                                    <FileUp className="h-4 w-4" />
                                                    {message.file_name}
                                                </a>
                                            )}
                                            <div className="flex items-center justify-between gap-2 mt-1">
                                                <p className={`text-xs ${isOwnMessage ? 'text-blue-100' : 'text-gray-500'}`}>
                                                    {formatMessageTime(message.created_at)}
                                                </p>
                                                {isOwnMessage && (
                                                    <button
                                                        onClick={() => handleDeleteMessage(message.id)}
                                                        className="text-blue-100 hover:text-red-300 transition"
                                                        title="Delete message"
                                                    >
                                                        <Trash2 className="h-3 w-3" />
                                                    </button>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            );
                        })
                    )}
                </div>

                {/* Message Input */}
                <div className="border-t border-gray-200 bg-white p-4">
                    <form onSubmit={handleSendMessage} className="space-y-3">
                        <div className="flex gap-2">
                            <Input
                                type="text"
                                value={messageBody}
                                onChange={(e) => setMessageBody(e.target.value)}
                                placeholder="Type your message..."
                                disabled={isLoading}
                                className="flex-1"
                            />
                            <label className="inline-flex items-center cursor-pointer">
                                <input
                                    type="file"
                                    onChange={handleFileSelect}
                                    disabled={isLoading}
                                    className="hidden"
                                />
                                <Button 
                                    type="button"
                                    variant="outline"
                                    disabled={isLoading}
                                    className="flex items-center gap-2"
                                >
                                    <FileUp className="h-4 w-4" />
                                    Attach
                                </Button>
                            </label>
                            <Button 
                                type="submit" 
                                disabled={isLoading || (!messageBody.trim() && !selectedFile)}
                                className="flex items-center gap-2 bg-blue-600 hover:bg-blue-700"
                            >
                                <Send className="h-4 w-4" />
                                Send
                            </Button>
                        </div>
                        {selectedFile && (
                            <div className="flex items-center gap-2 p-2 bg-gray-50 rounded">
                                <FileUp className="h-4 w-4 text-gray-600" />
                                <span className="text-sm text-gray-600 flex-1">{fileName}</span>
                                <button
                                    type="button"
                                    onClick={() => {
                                        setSelectedFile(null);
                                        setFileName('');
                                    }}
                                    className="text-gray-500 hover:text-red-600 transition"
                                >
                                    <Trash2 className="h-4 w-4" />
                                </button>
                            </div>
                        )}
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
