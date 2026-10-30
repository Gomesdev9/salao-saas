import type { InputHTMLAttributes, LabelHTMLAttributes, ReactNode, SelectHTMLAttributes, TextareaHTMLAttributes } from 'react';
import { cx } from '@/lib/format';

export function InputLabel({ value, className, children, ...props }: LabelHTMLAttributes<HTMLLabelElement> & { value?: ReactNode }) {
    return (
        <label {...props} className={cx('mb-1.5 block text-sm font-semibold text-tinta', className)}>
            {value ?? children}
        </label>
    );
}

export function InputError({ message, className }: { message?: string | string[] | null; className?: string }) {
    const messages = (Array.isArray(message) ? message : [message]).filter(Boolean);

    if (messages.length === 0) {
        return null;
    }

    return (
        <ul className={cx('mt-1.5 space-y-1 text-sm font-medium text-perigo', className)}>
            {messages.map((text) => (
                <li key={text}>{text}</li>
            ))}
        </ul>
    );
}

export function TextInput({ className, ...props }: InputHTMLAttributes<HTMLInputElement>) {
    return <input {...props} className={cx('campo', className)} />;
}

export function SelectInput({ className, ...props }: SelectHTMLAttributes<HTMLSelectElement>) {
    return <select {...props} className={cx('campo pr-10', className)} />;
}

export function TextareaInput({ className, ...props }: TextareaHTMLAttributes<HTMLTextAreaElement>) {
    return <textarea {...props} className={cx('campo', className)} />;
}

export function Checkbox({ label, ...props }: InputHTMLAttributes<HTMLInputElement> & { label: ReactNode }) {
    return (
        <label className="inline-flex min-h-11 items-center gap-2.5">
            <input type="checkbox" {...props} className="h-[18px] w-[18px] rounded border-linha-forte text-cafe focus:ring-caramelo" />
            <span className="text-sm text-tinta">{label}</span>
        </label>
    );
}

/** Campo com rótulo e mensagem de erro embaixo */
export function Field({ id, label, error, className, children }: { id?: string; label: ReactNode; error?: string; className?: string; children: ReactNode }) {
    return (
        <div className={className}>
            <InputLabel htmlFor={id} value={label} />
            {children}
            <InputError message={error} />
        </div>
    );
}
