@extends('layouts.main')

@section('tutulo', 'Detalle del cliente')

@section('contenido')

    <div class="container-fluid group-data-[content=boxed]:max-w-boxed mx-auto">
        <div class="flex flex-col gap-2 py-4 md:flex-row md:items-center">
            <div class="grow">
                <h5 class="text-16">Detalle del cliente</h5>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('customers.edit', $customer) }}"
                    class="text-white btn bg-custom-500 border-custom-500 hover:bg-custom-600">Editar</a>
                <a href="{{ route('customers.index') }}"
                    class="btn border-slate-200 text-slate-500 hover:text-custom-500">Volver</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <dl class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <dt class="mb-1 text-sm text-slate-500 dark:text-zink-200">Nombre</dt>
                        <dd class="font-medium text-slate-700 dark:text-zink-100">{{ $customer->name }}</dd>
                    </div>
                    <div>
                        <dt class="mb-1 text-sm text-slate-500 dark:text-zink-200">Numero de telefono</dt>
                        <dd class="font-medium text-slate-700 dark:text-zink-100">{{ $customer->phone }}</dd>
                    </div>
                    <div>
                        <dt class="mb-1 text-sm text-slate-500 dark:text-zink-200">Direccion particular</dt>
                        <dd class="font-medium text-slate-700 dark:text-zink-100">{{ $customer->address }}</dd>
                    </div>
                    <div>
                        <dt class="mb-1 text-sm text-slate-500 dark:text-zink-200">Numero de carnet de identidad</dt>
                        <dd class="font-medium text-slate-700 dark:text-zink-100">{{ $customer->identity_card }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

@endsection
