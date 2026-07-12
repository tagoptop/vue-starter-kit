import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';

export default function AppLogo() {
    const { branding } = usePage<SharedData>().props;
    const logoUrl = branding?.logoUrl ?? '/logo.svg';
    const companyName = branding?.companyName ?? 'Construction Supply';

    return (
        <>
            <div className="bg-sidebar-primary text-sidebar-primary-foreground flex aspect-square size-10 items-center justify-center rounded-md">
                <img src={logoUrl} alt={`${companyName} logo`} className="size-8 object-contain" />
            </div>
            <div className="ml-2 grid flex-1 text-left text-sm">
                <span className="mb-0.5 truncate leading-none font-bold text-base">{companyName}</span>
                <span className="truncate text-xs text-muted-foreground">Management System</span>
            </div>
        </>
    );
}
