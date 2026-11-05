import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import Avatar from '@/components/Avatar';
import { LinkButton } from '@/components/Buttons';
import Icon from '@/components/Icon';
import Page from '@/layouts/Page';
import { plural } from '@/lib/format';

type Client = {
    id: number;
    name: string;
    whatsapp: string | null;
    whatsappUrl: string | null;
    birthday: string | null;
    birthdaySoon: boolean;
    notes: string | null;
};

type Props = {
    clients: {
        data: Client[];
        from: number | null;
        to: number | null;
        total: number;
        currentPage: number;
        prevUrl: string | null;
        nextUrl: string | null;
    };
    search: string;
    total: number;
};

export default function ClientsIndex({ clients, search, total }: Props) {
    const [query, setQuery] = useState(search);

    return (
        <Page
            title="Clientes"
            subtitle={plural(total, 'cliente cadastrado', 'clientes cadastrados')}
            actions={
                <LinkButton href={route('clients.create')} icon="mais">
                    Novo cliente
                </LinkButton>
            }
        >
            <div className="cartao overflow-hidden">
                <form
                    className="border-b border-linha px-5 py-4"
                    onSubmit={(event) => {
                        event.preventDefault();
                        router.get(route('clients.index'), query ? { busca: query } : {}, { preserveState: true });
                    }}
                >
                    <label className="flex h-11 items-center gap-2.5 rounded-campo border border-linha-forte bg-cartao px-3.5 text-suave focus-within:border-caramelo focus-within:ring-1 focus-within:ring-caramelo">
                        <Icon name="busca" className="h-[18px] w-[18px]" />
                        <span className="sr-only">Buscar cliente</span>
                        <input
                            type="search"
                            name="busca"
                            value={query}
                            onChange={(event) => setQuery(event.target.value)}
                            placeholder="Buscar por nome ou WhatsApp"
                            className="h-full min-w-0 flex-1 border-0 bg-transparent p-0 text-[15px] text-tinta placeholder:text-suave/70 focus:ring-0"
                        />
                    </label>
                </form>

                {clients.data.length === 0 ? (
                    <p className="px-5 py-8 text-center text-suave">{search ? 'Nenhum cliente encontrado.' : 'Nenhum cliente cadastrado ainda.'}</p>
                ) : (
                    <>
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[680px] border-collapse text-sm">
                                <thead>
                                    <tr className="bg-areia text-left text-xs uppercase tracking-wider text-suave">
                                        <th className="px-5 py-3 font-semibold">Nome</th>
                                        <th className="px-3 py-3 font-semibold">WhatsApp</th>
                                        <th className="px-3 py-3 font-semibold">Aniversário</th>
                                        <th className="px-3 py-3 font-semibold">Observações</th>
                                        <th className="px-5 py-3">
                                            <span className="sr-only">Ações</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {clients.data.map((client) => (
                                        <tr key={client.id} className="border-t border-linha-clara">
                                            <td className="px-5 py-2.5">
                                                <span className="flex items-center gap-2.5">
                                                    <Avatar name={client.name} />
                                                    <span className="font-semibold">{client.name}</span>
                                                </span>
                                            </td>
                                            <td className="px-3 py-2.5">
                                                {client.whatsappUrl ? (
                                                    <a href={client.whatsappUrl} target="_blank" rel="noopener" className="font-medium text-cafe hover:text-cafe-escuro">
                                                        {client.whatsapp}
                                                    </a>
                                                ) : (
                                                    client.whatsapp || '—'
                                                )}
                                            </td>
                                            <td className="px-3 py-2.5">
                                                <span className="inline-flex items-center gap-1.5">
                                                    {client.birthday ?? '—'}
                                                    {client.birthdaySoon && <span className="rounded-full bg-mel px-2 py-0.5 text-xs font-semibold text-mel-texto">esta semana</span>}
                                                </span>
                                            </td>
                                            <td className="max-w-[260px] truncate px-3 py-2.5 text-suave">{client.notes || '—'}</td>
                                            <td className="px-5 py-1 text-right">
                                                <Link href={route('clients.edit', client.id)} className="inline-flex min-h-11 items-center font-semibold text-cafe hover:text-cafe-escuro">
                                                    Editar
                                                </Link>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        <div className="flex flex-wrap items-center justify-between gap-3 border-t border-linha px-5 py-3 text-sm text-suave">
                            <span>
                                Mostrando {clients.from}–{clients.to} de {clients.total}
                            </span>
                            {(clients.prevUrl || clients.nextUrl) && (
                                <div className="flex gap-1.5">
                                    {clients.prevUrl && (
                                        <Link href={clients.prevUrl} className="inline-flex h-11 items-center rounded-campo border border-linha px-3.5 font-semibold text-tinta hover:bg-areia">
                                            Anterior
                                        </Link>
                                    )}
                                    <span className="inline-flex h-11 items-center rounded-campo bg-tinta px-4 font-semibold text-white" aria-current="page">
                                        {clients.currentPage}
                                    </span>
                                    {clients.nextUrl && (
                                        <Link href={clients.nextUrl} className="inline-flex h-11 items-center rounded-campo border border-linha px-3.5 font-semibold text-tinta hover:bg-areia">
                                            Próxima
                                        </Link>
                                    )}
                                </div>
                            )}
                        </div>
                    </>
                )}
            </div>
        </Page>
    );
}
