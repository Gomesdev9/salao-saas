// Dados que o Laravel manda para toda tela (app/Http/Middleware/HandleInertiaRequests.php)
export type SharedProps = {
    appName: string;
    auth: {
        user: { id: number; name: string; email: string };
        salon: { name: string; plan: string; onTrial: boolean; trialDaysLeft: number };
    } | null;
    flash: { status: string | null };
};

/** Situação do horário (App\Enums\AppointmentStatus): valor, nome e as cores do selo. */
export type Status = { value: string; label: string; color: string };

/** Plano (App\Enums\Plan::toProps) */
export type PlanInfo = {
    value: string;
    label: string;
    description: string;
    price: number;
    formattedPrice: string;
    highlighted: boolean;
    professionalLimit: number | null;
    features: { text: string; soon: boolean }[];
};

export type Option = { value: string; label: string };
