<?php

namespace Laracl\Http\Controllers;

use Laracl\Models\AclUser;
use Laracl\Models\AclRole;
use Laracl\Models\AclUserPermission;
use Laracl\Traits\HasRolesStructure;
use Illuminate\Http\Request;
use Gate;
use DB;

class UsersPermissionsController extends Controller
{
    use HasRolesStructure; 

    /**
     * Exibe o formulário de configuração das permissões de acesso.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Aplica as permissões do banco na estrutura
        // de permissões do formulário
        $db_permissions = AclUserPermission::collectByUser($id);
        $this->populateStructure($db_permissions);

        $view = config('laracl.views.users-permissions.edit');
        return view($view)->with([
            'title'        => config('laracl.name'),
            'user'         => AclUser::find($id),
            'roles'        => $this->getRolesStructure(),
            'route_index'  => config('laracl.routes.users.index'),
            'route_create' => config('laracl.routes.users.create'),
            'route_update' => config('laracl.routes.users-permissions.update'),
            'route_groups' => config('laracl.routes.groups.index'),
        ]);
    }

    /**
     * Atualiza as permissões no banco de dados
     *
     * @param  \Illuminate\Http\Request $form
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $form, $id)
    {
        foreach ($form->roles as $slug => $perms) {

            $role = $this->getSyncedRole($slug);

            // Aplica as permissões para o usuário
            $model = AclUserPermission::firstOrNew([
                'user_id' => $id,
                'role_id' => $role->id,
                ]);

            $model->fill([
                'show'    => ($perms['show'] ?? 'no'),
                'create'  => ($perms['create'] ?? 'no'),
                'edit'    => ($perms['edit'] ?? 'no'),
                'delete'  => ($perms['delete'] ?? 'no'),
                ]);

            $model->save();
        }

        $route = config('laracl.routes.users-permissions.edit');
        return redirect()->route($route, $id);
    }
}