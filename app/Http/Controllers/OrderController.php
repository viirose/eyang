<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kris\LaravelFormBuilder\FormBuilderTrait;
use Auth;

use App\Shop;
use App\Order;
use App\User;
use App\Product;
use App\Forms\OrderForm;
use App\Helpers\Info;
use App\Helpers\Validator;

class OrderController extends Controller
{
    use FormBuilderTrait;

    /**
     * 列表
     *
     */
    public function index(Info $info)
    {
        $records = Order::
                where('shop_id', $info->id())
                ->latest()
                ->get();

        return view('orders.orders', compact('records'));
    }

    /**
     * 新订单
     *
     */
    public function create()
    {
         $form = $this->form(OrderForm::class, [
            'method' => 'POST',
            'url' => '/orders/store'
        ]);

        $title = '新订单';
        $icon = 'heart-o';

        return view('form', compact('form','title','icon'));
    }

    /**
     * 保存
     *
     */
    public function store(Request $request, Info $info)
    {
        $v = new Validator;
        if(!$v->checkMobile($request->mobile)) return redirect()->back()->withErrors(['mobile'=>'手机号不正确!'])->withInput();

        $user = User::where('mobile', $request->mobile)->first();
        if(!$user) return redirect()->back()->withErrors(['mobile'=>'无对应客户, 若需人工核验请打开用户管理'])->withInput();

        $product = Product::where('name', $request->name)->first();
        if(!$product) return redirect()->back()->withErrors(['name'=>'此产品名不存在!'])->withInput();


        $new['user_id'] = $user->id;
        $new['product_id'] = $product->id;
        $new['shop_id'] = $info->id();
        $new['amount'] = $request->amount;
        $new['created_by'] = Auth::id();

        Order::create($new);
        
        $color = 'success';
        $icon = 'heart-o';
        $text = '订单登记成功! <br><br><a href="/orders/create" class="btn btn-sm btn-success">继续登记</a>';

        return view('note', compact('color', 'icon', 'text'));

    }

    /**
     * 报备
     *
     */
    public function bb(Request $request)
    {
        // print_r($request->all());
        switch ($request->success) {
            case 'yes':
                # code...
                break;

            case 'no':
                return $this->bbFail($request->order_id);
                break;
            
            default:
                # code...
                break;
        }
    }

    /**
     * 报备
     *
     */
    private function bbForm($value='')
    {
        # code...
    }


    /**
     * 报备: 失败提示
     *
     */
    private function bbFail($id)
    {
        $order = Order::findOrFail($id);

        $color = 'warning';
        $icon = 'low-vision';
        $text = '您正将 '.$order->created_at.' 在 <strong>'.$order->product->name.'</strong> 审批的订单标记为未成功下款, 此产品将在您后续一个月登录中从产品列表中排除, 以呈现给您大通过率的产品,请确认! 若产品已下款, 请 <a href="/orders">返回</a> 报备  <br><br><a href="/orders/bb/fail/'.$id.'" class="btn btn-sm btn-warning">确认下款不成功</a>';

        return view('note', compact('color', 'icon', 'text'));
    }

    /**
     * 报备失败: 操作
     *
     */
    public function bbFailStore($id)
    {
        $order = Order::findOrFail($id);

        $order->update([
            'finish' => now(),
            // 'success' => false # 默认
        ]);

        $color = 'success';
        $icon = 'heart-o';
        $text = '您的操作已成功! <br><br><a href="/orders" class="btn btn-sm btn-success">返回</a>';

        return view('note', compact('color', 'icon', 'text'));

    }


    /**
     *
     *
     */
}




















