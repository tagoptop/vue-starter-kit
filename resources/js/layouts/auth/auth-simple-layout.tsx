import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';

interface AuthLayoutProps {
    children: React.ReactNode;
    name?: string;
    title?: string;
    description?: string;
}

export default function AuthSimpleLayout({ children, title, description }: AuthLayoutProps) {
    const { branding } = usePage<SharedData>().props;
    const logoUrl = branding?.logoUrl ?? '/logo.svg';
    const companyName = branding?.companyName ?? 'Construction Supply';

    return (
        <div className="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div className="w-full max-w-sm">
                <div className="flex flex-col gap-8">
                    <div className="flex flex-col items-center gap-4">
                        <Link href={route('home')} className="flex flex-col items-center gap-2 font-medium">
                            <div className="mb-1 flex h-12 w-12 items-center justify-center rounded-md">
                                <img src={logoUrl} alt={`${companyName} logo`} className="size-10 object-contain" />
                            </div>
                            <span className="sr-only">{title}</span>
                            <div className="text-center">
                                <div className="font-bold text-base">{companyName}</div>
                                <div className="text-xs text-muted-foreground">Management System</div>
                            </div>
                        </Link>

                        <div className="space-y-2 text-center">
                            <h1 className="text-xl font-medium">{title}</h1>
                            <p className="text-muted-foreground text-center text-sm">{description}</p>
                        </div>
                    </div>
                    {children}
                </div>
            </div>
        </div>
    );
}
