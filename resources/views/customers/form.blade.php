@extends('layouts.main')

@section('titulo', $customer->exists ? 'Editar cliente' : 'Nuevo cliente')

@section('contenido')
   
            <div class="container-fluid group-data-[content=boxed]:max-w-boxed mx-auto">
                <div class="flex flex-col gap-2 py-4 md:flex-row md:items-center">
                    <div class="grow">
                        <h5 class="text-16">{{ $customer->exists ? 'Editar cliente' : 'Nuevo cliente' }}</h5>
                    </div>
                    <a href="{{ route('customers.index') }}" class="text-slate-500 btn border-slate-200 hover:text-custom-500">Volver al listado</a>
                </div>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ $customer->exists ? route('customers.update', $customer) : route('customers.store') }}" class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            @csrf
                            @if ($customer->exists)
                                @method('PUT')
                            @endif

                            @php($fieldPrefix = '')
                            @include('customers._fields')

                            <div class="flex items-end gap-2 md:col-span-2">
                                <button type="submit" class="text-white btn bg-custom-500 border-custom-500 hover:bg-custom-600">Guardar cliente</button>
                                <a href="{{ route('customers.index') }}" class="btn border-slate-200 text-slate-500 hover:text-custom-500">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
       
@endsection
