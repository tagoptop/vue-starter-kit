import AppLogoIcon from './app-logo-icon';

export default function AppLogo() {
    return (
        <>
            <div className="bg-sidebar-primary text-sidebar-primary-foreground flex aspect-square size-10 items-center justify-center rounded-md">
                <img src="/logo.svg" alt="CSMS Logo" className="size-8" />
            </div>
            <div className="ml-2 grid flex-1 text-left text-sm">
                <span className="mb-0.5 truncate leading-none font-bold text-base">Construction Supply</span>
                <span className="truncate text-xs text-muted-foreground">Management System</span>
            </div>
        </>
    );
}
