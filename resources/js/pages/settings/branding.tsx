import { useState } from 'react';
import { Head } from '@inertiajs/react';
import { router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { type BreadcrumbItem } from '@/types';

interface Props {
    logoUrl: string;
    companyName: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Company Branding',
        href: '/settings/branding',
    },
];

export default function Branding({ logoUrl, companyName }: Props) {
    const [formData, setFormData] = useState({
        company_name: companyName,
        logo: null as File | null,
    });
    const [preview, setPreview] = useState<string>(logoUrl);
    const [isLoading, setIsLoading] = useState(false);

    const handleCompanyNameChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setFormData(prev => ({
            ...prev,
            company_name: e.target.value,
        }));
    };

    const handleLogoChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            setFormData(prev => ({
                ...prev,
                logo: file,
            }));
            
            // Create preview
            const reader = new FileReader();
            reader.onloadend = () => {
                setPreview(reader.result as string);
            };
            reader.readAsDataURL(file);
        }
    };

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        setIsLoading(true);

        const data = new FormData();
        data.append('company_name', formData.company_name);
        if (formData.logo) {
            data.append('logo', formData.logo);
        }

        router.post(route('branding.update'), data, {
            onFinish: () => setIsLoading(false),
        });
    };

    const handleReset = () => {
        if (confirm('Are you sure you want to reset branding to defaults?')) {
            setIsLoading(true);
            router.delete(route('branding.reset'), {
                onFinish: () => setIsLoading(false),
            });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Company Branding" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall 
                        title="Company Branding" 
                        description="Customize your company logo and name displayed across the application"
                    />

                    <form onSubmit={handleSubmit} className="space-y-6">
                        {/* Company Name Field */}
                        <div className="space-y-2">
                            <label className="text-sm font-medium">Company Name</label>
                            <Input
                                type="text"
                                name="company_name"
                                value={formData.company_name}
                                onChange={handleCompanyNameChange}
                                placeholder="Enter company name"
                                className="w-full"
                            />
                            <p className="text-xs text-muted-foreground">
                                This name appears in the navigation and branding elements
                            </p>
                        </div>

                        {/* Logo Preview */}
                        <div className="space-y-2">
                            <label className="text-sm font-medium">Current Logo</label>
                            <div className="flex items-center gap-4">
                                <div className="flex h-24 w-24 items-center justify-center rounded-lg border border-dashed">
                                    <img 
                                        src={preview} 
                                        alt="Logo preview" 
                                        className="max-h-20 max-w-20 object-contain"
                                    />
                                </div>
                                <div className="text-sm text-muted-foreground">
                                    <p>Current logo is {logoUrl === '/logo.svg' ? 'default' : 'custom'}</p>
                                </div>
                            </div>
                        </div>

                        {/* Logo Upload */}
                        <div className="space-y-2">
                            <label className="text-sm font-medium">Upload New Logo</label>
                            <div className="flex gap-2">
                                <Input
                                    type="file"
                                    name="logo"
                                    onChange={handleLogoChange}
                                    accept="image/svg+xml,image/png,image/jpeg,image/gif,image/webp"
                                    className="flex-1"
                                />
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Supported formats: SVG, PNG, JPG, GIF, WebP. Max size: 2MB
                            </p>
                        </div>

                        {/* Action Buttons */}
                        <div className="flex gap-2 pt-4">
                            <Button 
                                type="submit" 
                                disabled={isLoading}
                                className="bg-blue-600 hover:bg-blue-700"
                            >
                                {isLoading ? 'Saving...' : 'Save Branding'}
                            </Button>
                            <Button
                                type="button"
                                onClick={handleReset}
                                disabled={isLoading || logoUrl === '/logo.svg'}
                                variant="outline"
                            >
                                Reset to Default
                            </Button>
                        </div>
                    </form>

                    {/* Info Box */}
                    <div className="rounded-lg border border-blue-200 bg-blue-50 p-4">
                        <p className="text-sm text-blue-900">
                            <strong>Note:</strong> Changes to company branding will be reflected across the entire application for all users in their next session.
                        </p>
                    </div>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
