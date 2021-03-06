<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kris\LaravelFormBuilder\FormBuilderTrait;
use Image;
use Auth;

use App\Forms\ProductForm;
use App\Product;
use App\Conf;
use App\Org;
use App\Order;
use App\Helpers\Info;
use App\Helpers\Link;
use App\Helpers\Role;
use App\Helpers\Picker;
use App\Helpers\Filter;

class ProductController extends Controller
{
    use FormBuilderTrait;

    private $info;
    private $role;

    public function index(Info $info, Role $role)
    {
        $this->info = $info;
        $this->role = $role;

        $records = Conf::where('key', 'product_type')
                        ->whereHas('products', function($q1){
                            $q1->whereNotIn('org_id', $this->info->lackOrgIds());

                            if(!$this->role->issuer()) {
                                $q1->whereNotNull('img');
                                $q1->where('show', true);
                            }
                        })
                        ->with(['products' => function($query){
                            $query->whereNotIn('org_id', $this->info->lackOrgIds());

                            if(!$this->role->issuer()) {
                                $query->whereNotNull('img');
                                $query->where('show', true);
                            }

                            $query->orderBy('fs','desc');
                            $query->latest();
                        }])
                        ->get();

        return view('products.products', compact('records'));
    }

    /**
     * 单个产品
     *
     */
    public function show($id, Filter $f)
    {
        $record = Product::findOrFail($id);

        if(!$f->onLine($record)) abort('403');

        return view('products.show', compact('record'));
    }

    /**
     * 新产品
     *
     */
    public function create(Role $role)
    {
        if(!$role->admin() && !$role->issuer()) abort(403);

         $form = $this->form(ProductForm::class, [
            'method' => 'POST',
            'url' => '/products/store'
        ]);

        $title = '新产品';
        $icon = 'money';

        return view('form', compact('form','title','icon'));
    }

    /**
     * 验证
     *
     */
    public function store(Request $request, Link $link, Role $role)
    {
        if(!$role->admin() && !$role->issuer()) abort(403);

        $exists = Product::where('name', $request->name)
                        ->first();

        if($exists) return redirect()->back()->withErrors(['name'=>'此产品名称已存在!'])->withInput();


        $new = $request->all();
        $new['created_by'] = Auth::id();

        try {
            $templet = $link->buildProductTemplet(intval($request->org_id), $request->url);
            // $param = $link->getParam(intval($request->org_id), $request->url);
        } catch (Exception $e) {
            abort('403');
        }

        if(!$templet) abort('403');

        $new['config->templet'] = $templet;

        $record = Product::create($new);

        return view('img', compact('record'));

    }

    /**
     * 图片
     *
     */
    public function imgStore(Request $request, Role $role)
    {
        if(!$role->admin() && !$role->issuer()) abort(403);
        
        $img = $request->file('avatar');
        $id = $request->id;

        $exists = Product::find($id);
        if(!$exists) abort('404');

        $new_img = 'storage/storage/app/img/'.$id.'-'.time().'.png';

        $image = Image::make($img)
                ->save($new_img);

        if($exists->img) unlink($exists->img);

        $exists->update(['img' => $new_img]);

        echo '200';
    }

    /**
     * edit
     *
     */
    public function edit($id, Role $role)
    {
        if(!$role->admin() && !$role->issuer()) abort(403);

        $record = Product::findOrFail($id);

        $form = $this->form(ProductForm::class, [
            'method' => 'POST',
            'model' => $record,
            'url' => '/products/update/'.$id
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
        if(!$role->admin() && !$role->issuer()) abort(403);

        $all = $request->all();

        $record = Product::findOrFail($id);

        $record->update($all);

        return view('img', compact('record'));
    }

    /**
     * 放水
     *
     */
    public function fs($id, Role $role)
    {
        if(!$role->admin() && !$role->issuer()) abort(403);

       $target = Product::findOrFail($id);
       $target->update([
            'fs' => true,
       ]);
       return redirect()->back();
    }

    /**
     * 放水
     *
     */
    public function unfs($id, Role $role)
    {
        if(!$role->admin() && !$role->issuer()) abort(403);

        $target = Product::findOrFail($id);
        $target->update([
            'fs' => false,
        ]);
        return redirect()->back();
    }

    /**
     * 上架
     *
     */
    public function on($id, Role $role)
    {
        if(!$role->admin() && !$role->issuer()) abort(403);

       $target = Product::findOrFail($id);
       $target->update([
            'show' => true,
       ]);
       return redirect()->back();
    }

    /**
     * 下架
     *
     */
    public function off($id, Role $role, Picker $p)
    {
        if(!$role->admin() && !$role->issuer()) abort(403);

        $target = Product::findOrFail($id);

        // 清理明星
        $p->clearIfSlide($id);

        $target->update([
            'show' => false,
        ]);
        return redirect()->back();
    }

    /**
     * 删除
     *
     */
    public function delete($id, Role $role, Picker $p)
    {
        if(!$role->admin() && !$role->issuer()) abort('403');

        $target = Product::findOrFail($id);

        $order = Order::where('product_id', $id)
                        ->whereDate('created_at', today()->toDateString())
                        ->count();
                        
        if($order > 0) abort('403');


        if($target->img) unlink($target->img);

        $p->clearIfSlide($id);

        $target->delete();

        return redirect()->back();
    }

    /**
     * 设置为明星
     *
     */
    public function slide($id, Role $role, Picker $p)
    {
        if(!$role->admin() && !$role->issuer()) abort('403');

        $p->setSlide($id);
        return redirect('/');
    }

    /**
     * 设置为明星
     *
     */
    public function removeSlide(Role $role, Picker $p)
    {
        if(!$role->admin() && !$role->issuer()) abort('403');

        $p->removeSlide();
        return redirect('/');
    }

    /**
     * 
     *
     */
}


















