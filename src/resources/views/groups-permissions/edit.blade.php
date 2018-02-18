
@component('laracl::document')

    @slot('title') Permissoes para "{{ $group->name }}" @endslot

    @aclock('groups-permissions.show')

        <div class="row mb-3">

            <div class="col">

                @acl_action('groups.show', route($route_index), 'Grupos de Acesso')

            </div>

            <div class="col text-right justify-content-end">

                @acl_action('groups.create', route($route_create), 'Novo Grupo')

            </div>
            
        </div>

        <form method="post" action="{{ route($route_update, $group->id) }}">

            <div class="row mt-3">

                <div class="col">

                    {{ csrf_field() }}

                    {{ method_field('PUT') }} 
                    {{-- https://laravel.com/docs/5.5/controllers#resource-controllers --}}

                    <table class="table table-striped table-bordered">

                        <thead>

                            <th>Área da Loja</th>
                            
                            <th>Ver</th>

                            <th>Criar</th>

                            <th>Editar</th>

                            <th>Excluir</th>

                        </thead>

                        <tbody>

                            @foreach($roles as $route => $item)

                                <tr>
                                    <td>
                                        {{ $item['label'] }}

                                        {{-- É necessário para que a função sempre exista na matriz, 
                                        mesmo quando não existirem permissões ativas --}}
                                        <input type="hidden" name="roles[{{ $route }}][exists]" value="1">
                                    </td>

                                    @foreach($item['roles'] as $role => $role_value)

                                        @php
                                        $role_name = "roles[{$route}][{$role}]";
                                        @endphp

                                        <td>
                                            @if($role_value != null)

                                                <input type="checkbox" name="{{ $role_name }}" class="check-toggle" 
                                                       data-on-text="Sim" data-off-text="Não"
                                                       value="yes" {{ old_check($role_name, $role_value, 'yes') }}>

                                            @endif

                                        </td>

                                    @endforeach
                                    
                                </tr>

                            @endforeach
                            
                        </tbody>
                    </table>

                </div>

            </div>

            <div class="row">

                <div class="col text-right">

                    @acl_submit_lg('groups-permissions.edit', 'Aplicar Permissões')

                </div>

            </div>

        </form>

    @endaclock

@endcomponent