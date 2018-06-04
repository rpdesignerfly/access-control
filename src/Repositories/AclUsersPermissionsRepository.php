<?php
namespace Laracl\Repositories;

use Laracl\Models\AclUserPermission;
use Laracl\Repositories\AclRolesRepository;

class AclUsersPermissionsRepository extends IRepository
{
    protected $model_class = AclUserPermission::class;

    /**
     * Atualiza as permissões de um usuário existente.
     *
     * @param  int    $id
     * @param  array  $data
     * @return bool
     */
    public function update($id, array $data)
    {
        $results = [];
        foreach ($data['permissions'] as $slug => $perms) {

            $role = (new AclRolesRepository)->findBySlug($slug);

            // Aplica as permissões para o usuário
            $model = AclUserPermission::firstOrNew([
                'user_id' => $id,
                'role_id' => $role->id,
                ]);

            $model->fill([
                'create' => ($perms['create'] ?? 'no'),
                'read'   => ($perms['read'] ?? 'no'),
                'update' => ($perms['update'] ?? 'no'),
                'delete' => ($perms['delete'] ?? 'no'),
                ]);

            $results[] = $model->save();
        }

        $results = \array_unique($results);
        return count($results) == 1 && $results[0] == true;
    }

    /**
     * Devolve todos os registros.
     * Se $take for false então devolve todos os registros
     * Se $paginate for true retorna uma instânca do Paginator
     *
     * @param  int  $user_id
     * @param  bool $take
     * @param  bool $paginate
     * @return EloquentCollection|Paginator
     */
    public function getAllByUserID(int $user_id, $take = false, bool $paginate = false)
    {
        $query = $this->newQuery()->where('user_id', $user_id);
        return $this->doQuery($query, $take, $paginate);
    }

    /**
    * Este método devolve a estrutura de permissões para
    * a geração do formulário de edição.
    * Se $allows_null for true e o usuário não possuir permissões,
    * o valor null será retornado, caso contrário, uma estrutura
    * com valores desativados será retornada.
    *
    * @param  int  $user_id
    * @param  bool $allows_null
    * @return array|null
    */
    public function getStructure($user_id, $allows_null = false)
    {
        $permissions = [];

        $collection = $this->getAllByUserID($user_id);
        if ($collection->count() > 0) {
            // Apenas as habilidades do usuário
            foreach ($collection as $item) {
                $permissions[$item->role->slug] = [
                    'create' => $item->create,
                    'read'   => $item->read,
                    'update' => $item->update,
                    'delete' => $item->delete,
                ];
            }
        } elseif($allows_null == true) {
            return null;
        }

        $structure = [];

        $all_abilities = (new AclRolesRepository)->getStructure();
        foreach ($all_abilities as $role => $item) {

            foreach ($item['permissions'] as $ability => $nullable) {
                if ($nullable !== null) {
                    $structure[$role]['label'] = $all_abilities[$role]['label'];
                    $structure[$role]['permissions'][$ability] = isset($permissions[$role])
                        ? $permissions[$role][$ability] : 'no';
                }
            }
        }

        return $structure;
    }
}
