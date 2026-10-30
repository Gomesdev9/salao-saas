import { Link } from '@inertiajs/react';
import type { ButtonHTMLAttributes, ComponentProps } from 'react';
import { cx } from '@/lib/format';
import Icon from './Icon';

const base = 'inline-flex h-11 items-center justify-center gap-2 rounded-campo text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-2';

/** Link com cara de botão. variant="secondary" é o botão claro com borda. */
export function LinkButton({ variant = 'primary', icon, className, children, ...props }: ComponentProps<typeof Link> & { variant?: 'primary' | 'secondary'; icon?: string }) {
    const colors = variant === 'primary' ? 'bg-cafe text-white hover:bg-cafe-escuro' : 'border border-linha bg-cartao text-tinta hover:bg-areia';

    return (
        <Link {...props} className={cx(base, 'px-5 focus:ring-caramelo', colors, className as string)}>
            {icon && <Icon name={icon} className="h-[18px] w-[18px]" />}
            {children}
        </Link>
    );
}

export function PrimaryButton({ className, type = 'submit', ...props }: ButtonHTMLAttributes<HTMLButtonElement>) {
    return <button type={type} {...props} className={cx(base, 'bg-cafe px-5 text-white hover:bg-cafe-escuro focus:ring-caramelo disabled:opacity-50', className)} />;
}

export function SecondaryButton({ className, type = 'button', ...props }: ButtonHTMLAttributes<HTMLButtonElement>) {
    return <button type={type} {...props} className={cx(base, 'border border-linha bg-cartao px-4 text-tinta hover:bg-areia focus:ring-caramelo disabled:opacity-50', className)} />;
}

export function DangerButton({ className, type = 'submit', ...props }: ButtonHTMLAttributes<HTMLButtonElement>) {
    return <button type={type} {...props} className={cx(base, 'bg-perigo px-5 text-white hover:bg-red-800 focus:ring-perigo', className)} />;
}
