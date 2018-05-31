<?php

namespace Laracl\Http\Controllers;

use Illuminate\Http\Request;
use Laracl\Models;

class IPermissionsController extends Controller
{
    protected $roles = null;

    /**
     * Este método gera uma lista de opções para o formulário.
     * A fonte de rotas é obtida diretamente do Gate do Laravel.
     *
     * @return array
     */
    protected function getRolesStructure()
    {
        if ($this->roles !== null) {
            return $this->roles;
        }

        $abilities = config('laracl.roles');

        // Habilidades resistradas
        foreach (\Gate::abilities() as $ability => $closure) {

            $nodes = explode('.', $ability);
            $route = $nodes[0];
            $role  = $nodes[1];

            if ( !isset($this->roles[$route]) ) {
                $this->roles[$route] = [
                    'label' => $abilities[$route]['label']
                ];
            }

            if ( !isset($this->roles[$route]['roles']) ) {
                $this->roles[$route]['roles'] = [
                    'create' => null,
                    'read'   => null,
                    'update' => null,
                    'delete' => null,
                ];
            }

            // Não nulos aparecerão no formulário
            // Nulos terão o checkbox ocultado
            $this->roles[$route]['roles'][$role] = '';
        }

        return $this->roles ?? [];
    }

    /**
     * Este método preenche a estrutura de rotas com as
     * informações armazenadas no banco de dados.
     *
     * @param \Illuminate\Database\Eloquent\Collection $collection
     * @return array
     */
    protected function populateStructure($collection)
    {
        if (is_array($collection)){
            $collection = collect($collection);
        }
        
        $permissions = [];
        foreach ($collection as $item) {
            $permissions[$item->role->slug] = [
                'create' => $item->create,
                'read'   => $item->read,
                'update' => $item->update,
                'delete' => $item->delete,
            ];
        }

        // Aplica as permissões do banco
        // na estrutura de habilidades existente
        foreach ($this->getRolesStructure() as $route => $item) {
            foreach ($item['roles'] as $role => $nullable) {
                if ($nullable !== null) {
                    $this->roles[$route]['roles'][$role] = isset($permissions[$route])
                        ? $permissions[$route][$role] : 'no';
                }
            }
        }
    }

    /**
     * Devolce uma função a partir de ssua slug
     * No processo, sincroniza as informações do arquivo de
     * configuração com o banco de dados.
     *
     * @param string $slug
     * @return \Laracl\Models\AclRole
     */
    protected function getSyncedRole($slug)
    {
        $info = config("laracl.roles.{$slug}");

        $role = Models\AclRole::where('slug', $slug)->first();

        // Se a função nunca foi setada,
        // deve ser criada
        if ($role == NULL) {

            $role = Models\AclRole::create([
                'name'        => $info['label'],
                'slug'        => $slug,
                'description' => $info['description'] ?? ''
            ]);
        }
        else {
            $role->fill([
                'name'        => $info['label'],
                'description' => $info['description'] ?? ''
            ]);
            $role->save();
        }

        return $role;
    }
}
