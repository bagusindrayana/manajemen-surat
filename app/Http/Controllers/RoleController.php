<?php

namespace App\Http\Controllers;

use App\Helpers\UserLogHelper;
use App\Models\GroupPermission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //check if user can View Role
        if (!auth()->user()->can('View Role'))
            return abort(403, 'Anda tidak memiliki cukup hak akses');
        $roles = Role::filtersInput(null, 'search')->paginate(10);
        $data = [
            'title' => 'Jabatan/Role',
            'roles' => $roles
        ];

        return view('role.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $groups = GroupPermission::with('permissions')->get();
        $data = [
            'title' => 'Tambah Jabatan/Role',
            'groups' => $groups
        ];
        return view('role.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:150|unique:roles,name',
            'description' => 'nullable|max:255',
            'permission_ids' => 'required|array'
        ]);
        DB::beginTransaction();
        try {
            $role = Role::create([
                'name' => $request->name,
                'description' => $request->description
            ]);
            $permission_ids = $request->permission_ids;
            $role->permissions()->attach($permission_ids);
            UserLogHelper::create('menambah data role baru dengan nama : ' . $role->name);
            DB::commit();
            return redirect()->route('role.index')->with('success', 'Data berhasil disimpan');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('role.index')->with('error', 'Data gagal disimpan')->withInput($request->all());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role)
    {
        $data = [
            'title' => 'Detail Jabatan/Role',
            'role' => $role
        ];
        return view('role.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function edit(Role $role)
    {
        $groups = GroupPermission::with('permissions')->get();
        return view('role.edit', compact('groups', 'role'), [
            'title' => 'Ubah Jabatan/Role'
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|max:150|unique:roles,name,' . $role->id,
            'description' => 'nullable|max:255',
            'permission_ids' => 'required|array'
        ]);
        DB::beginTransaction();
        try {
            $role->update([
                'name' => $request->name,
                'description' => $request->description
            ]);
            $permission_ids = $request->permission_ids;
            $role->permissions()->sync($permission_ids);
            UserLogHelper::create('mengubah role : ' . $role->name);
            DB::commit();
            return redirect()->route('role.index')->with('success', 'Data berhasil disimpan');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('role.index')->with('error', 'Data gagal disimpan')->withInput($request->all());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {
        if ($role->users()->count() > 0) {
            return redirect()->route('role.index')->with('error', 'Data gagal dihapus, karena masih ada user yang menggunakan role ini');
        }
        DB::beginTransaction();
        try {
            $role->delete();
            UserLogHelper::create('menghapus role : ' . $role->name);
            DB::commit();
            return redirect()->route('role.index')->with('success', 'Data berhasil dihapus');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('role.index')->with('error', 'Data gagal dihapus');
        }
    }
}