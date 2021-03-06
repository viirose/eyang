<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kris\LaravelFormBuilder\FormBuilderTrait;

use App\Forms\OrgForm;
use App\Org;
use App\Helpers\Role;

class OrgController extends Controller
{
    use FormBuilderTrait;


    public function index()
    {
        $records = Org::all();
        return view('orgs.orgs', compact('records'));
    }

    /**
     * 新上家
     *
     */
    public function create(Role $role)
    {
        if(!$role->root()) abort('403');

        $form = $this->form(OrgForm::class, [
            'method' => 'POST',
            'url' => '/orgs/store'
        ]);

        $title = '新供应商';
        $icon = 'cubes';

        return view('form', compact('form','title','icon'));
    }

    /**
     * 保存
     *
     */
    public function store(Request $request, Role $role)
    {
        if(!$role->root()) abort('403');
        
        // $config = [
        //     'templet' => $request->templet,
        //     'shop' => $request->shop,
        //     'product' => $request->product,
        // ];

        // // 获取报备字段
        // $bb = $request->except(['name', 'code', 'templet', 'shop','product','_token']);

        // foreach ($bb as $key => $value) {
        //     if($request->$key) $config = array_add($config, $key, $value);
        // }

        $new = [
            'name' => $request->name,
            'code' => $request->code,
            'config' => $request->config,
        ];

        Org::create($new);

        $color = 'success';
        $icon = 'bank';
        $text = '操作已成功!';

        return view('note', compact('color', 'icon', 'text'));
    }

    /**
     * 修改
     *
     */
    public function edit(Role $role, $id)
    {
        if(!$role->root()) abort(403);

        $record = Org::findOrFail($id);

        $form = $this->form(OrgForm::class, [
            'method' => 'POST',
            'model' => $record,
            'url' => '/orgs/update/'.$id
        ]);

        $title = 'Edit: '.$record->part_no;
        $icon = 'wrench';

        return view('form', compact('form','title','icon'));
    }

    /**
     * update
     *
     */
    public function update(Request $request, $id, Role $role)
    {
        if(!$role->root()) abort(403);

        $all = $request->all();

        $record = Org::findOrFail($id);

        $record->update($all);

        $color = 'success';
        $icon = 'bank';
        $text = '操作已成功!';

        return view('note', compact('color', 'icon', 'text'));
    }

    /**
     * 
     *
     */
}






















