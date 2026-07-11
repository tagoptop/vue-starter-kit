import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { type BreadcrumbItem } from '@/types';
import { MessageSquare } from 'lucide-react';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
}

interface Props {
    customers: User[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Messages',
        href: '/conversations',
    },
    {
        title: 'New Conversation',
        href: '/conversations/create',
    },
];

export default function CreateConversation({ customers }: Props) {
    const [customerId, setCustomerId] = useState('');
    const [subject, setSubject] = useState('');
    const [isLoading, setIsLoading] = useState(false);
    const [searchTerm, setSearchTerm] = useState('');

    const filteredCustomers = customers.filter(customer =>
        customer.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        customer.email.toLowerCase().includes(searchTerm.toLowerCase())
    );

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        if (!customerId) {
            alert('Please select a customer');
            return;
        }

        setIsLoading(true);

        router.post(route('conversations.store'), {
            customer_id: customerId,
            subject: subject || null,
        }, {
            onFinish: () => setIsLoading(false),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="New Conversation" />
            
            <div className="max-w-2xl mx-auto">
                <div className="bg-white rounded-lg shadow p-6">
                    <div className="flex items-center gap-3 mb-6">
                        <MessageSquare className="h-6 w-6" />
                        <h1 className="text-2xl font-bold">Start New Conversation</h1>
                    </div>

                    <form onSubmit={handleSubmit} className="space-y-6">
                        {/* Customer Selection */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Select Customer
                            </label>
                            
                            {/* Search Box */}
                            <Input
                                type="text"
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                placeholder="Search by name or email..."
                                className="mb-2"
                            />

                            {/* Customer List */}
                            <div className="border border-gray-300 rounded-lg max-h-64 overflow-y-auto">
                                {filteredCustomers.length === 0 ? (
                                    <div className="p-4 text-center text-gray-500">
                                        {customers.length === 0 
                                            ? 'No customers available' 
                                            : 'No customers match your search'}
                                    </div>
                                ) : (
                                    filteredCustomers.map((customer) => (
                                        <button
                                            key={customer.id}
                                            type="button"
                                            onClick={() => {
                                                setCustomerId(customer.id.toString());
                                                setSearchTerm('');
                                            }}
                                            className={`w-full text-left p-3 border-b border-gray-200 last:border-b-0 hover:bg-gray-50 transition ${
                                                customerId === customer.id.toString() 
                                                    ? 'bg-blue-50 border-l-4 border-l-blue-600' 
                                                    : ''
                                            }`}
                                        >
                                            <div className="font-medium">{customer.name}</div>
                                            <div className="text-sm text-gray-600">{customer.email}</div>
                                        </button>
                                    ))
                                )}
                            </div>

                            {customerId && (
                                <div className="mt-3 p-3 bg-blue-50 rounded border border-blue-200">
                                    <p className="text-sm text-blue-900">
                                        Selected: <span className="font-semibold">
                                            {customers.find(c => c.id.toString() === customerId)?.name}
                                        </span>
                                    </p>
                                </div>
                            )}
                        </div>

                        {/* Subject (Optional) */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Conversation Subject (Optional)
                            </label>
                            <Input
                                type="text"
                                value={subject}
                                onChange={(e) => setSubject(e.target.value)}
                                placeholder="e.g., Order inquiry, Support request..."
                                maxLength={255}
                            />
                        </div>

                        {/* Submit */}
                        <div className="flex gap-3">
                            <Button
                                type="submit"
                                disabled={isLoading || !customerId}
                                className="bg-blue-600 hover:bg-blue-700"
                            >
                                {isLoading ? 'Creating...' : 'Start Conversation'}
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => window.history.back()}
                                disabled={isLoading}
                            >
                                Cancel
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
