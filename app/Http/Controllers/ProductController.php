<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kris\LaravelFormBuilder\FormBuilderTrait;
use Image;
use Auth;

use App\Forms\ProductForm;
use App\Product;
use App\Conf;
use App\Helpers\Info;

class ProductController extends Controller
{
    use FormBuilderTrait;

    private $info;

    public function index(Info $info)
    {
        $this->info = $info;

        $records = Conf::where('key', 'product_type')
                        ->whereHas('products', function($q1){
                            $q1->whereNotNull('img');
                            $q1->whereNotIn('org_id', $this->info->lackOrgIds());
                            $q1->where('show', true);
                        })
                        ->with(['products' => function($query){
                            $query->whereNotNull('img');
                            $query->whereNotIn('org_id', $this->info->lackOrgIds());
                            $query->where('show', true);
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
    public function show($id)
    {
        $record = Product::findOrFail($id);
        return view('products.show', compact('record'));
    }

    /**
     * 新产品
     *
     */
    public function create()
    {
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
    public function store(Request $request)
    {
        $exists = Product::where('name', $request->name)
                        ->first();

        if($exists) return redirect()->back()->withErrors(['name'=>'此名称已存在!'])->withInput();


        $new = $request->all();
        $new['created_by'] = Auth::id();

        $record = Product::create($new);

        return view('img', compact('record'));

    }

    /**
     * 图片
     *
     */
    public function imgStore(Request $request)
    {
        // if(!$role->admin()) abort(403);
        
        $img = $request->file('avatar');
        $id = $request->id;

        $exists = Product::find($id);
        if(!$exists) abort('404');

        $new_img = 'storage/app/img/'.$id.'-'.time().'.png';

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
    public function edit($id)
    {
        // if(!$role->admin()) abort(403);

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
    public function update(Request $request, $id)
    {
        // if(!$role->admin()) abort(403);

        $all = $request->all();

        $record = Product::findOrFail($id);

        $record->update($all);

        return view('img', compact('record'));
    }
}


















