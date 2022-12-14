<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Mail\orderConfirmation;
use App\Mail\suscriber;
use App\Models\Banner;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Miscellaneous;
use App\Models\Order;
use App\Models\Product;
use App\Models\Slider;
use App\Models\Suscribers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use RealRashid\SweetAlert\Facades\Alert;

use Illuminate\Support\Facades\Auth;

use PHPUnit\Framework\Constraint\IsEmpty;

class HomeBaseController extends FrontendBaseController
{

    protected $view ='frontend.';
//    protected $route ='category.';
    protected $title;

    public function nav(){


                $data['cart']=Cart::where('user_id',Auth::user()->id)->count();

//        dd($data['count']);

        return view($this->__loadDataToView($this->view . 'includes.nav'),compact('data'));


    }


    public function index()
    {
        $this->title= 'Home';
        $data = [];
//        $data['row'] =User::findOrFail($id);
        $data['cart']=Cart::where('user_id',Auth::user()->id)->count();
        $data['product']=Product::latest()->where('status',1)->take(6)->get();
        $data['category']=Category::where('status',1)->get();
        $data['banner'] = Banner::latest()->take(2)->where('status',1)->get();
        $data['slider'] = Slider::latest()->take(3)->where('status',1)->get();
        $data['footer']=Miscellaneous::all();
        return view($this->__loadDataToView($this->view . 'home'),compact('data'));
    }
    public function main()
    {
        $this->title= 'Home';
        $data['product']=Product::latest()->where('status',1)->take(6)->get();;
        $data['category']=Category::where('status',1)->get();
        $data['banner'] = Banner::latest()->take(2)->where('status',1)->get();
        $data['slider'] = Slider::latest()->take(3)->where('status',1)->get();
        $data['footer']=Miscellaneous::all();
        return view($this->__loadDataToView( 'welcome'),compact('data'));
    }
    public function single($id)
    {
        $data = [];

//        $data['cart']=Cart::where('user_id',Auth::user()->id)->count();
        $this->title= 'Single';
        $data['pro']=Product::where('id', $id)->where('status',1)->first();
        $data['category']=Category::where('status',1)->get();
        $data['footer']=Miscellaneous::all();

        return view($this->__loadDataToView($this->view . 'pages.single'),compact('data'));
    }
    public function addToCart(Request $request,$id)
    {

        $product = Product::findOrFail($id);
        $cart= session()->get('cart', []);
        if(isset($cart[$id])) {
            $cart[$id]->quantity=$cart[$id]->quantity+$request->quantity;
            $cart[$id]->update();
        } else {
            $cart[$id] = Cart::create( [
                'user_id' => $request->user_id,
                'product_id' => $request->product_id,
                "name" => $product->name,
                "quantity" =>$request->quantity,
                "price" => $product->price,
                "image" => $product->image
            ]);
        }
        session()->put('cart', $cart);
        Alert::success('Congrats', 'Product added on your cart');

        return redirect()->back();
    }
    public function cartlist()
    {

        $this->title= 'Cart';
        $data = [];
        $data['footer']=Miscellaneous::all();

        $data['cart']=Cart::where('user_id',Auth::user()->id)->count();

        $data['cartItems'] = Cart::where('user_id',Auth::user()->id)->get();
//        dd($data['cartItems']);
        $data['category']=Category::all();

        return view($this->__loadDataToView($this->view . 'pages.cart'),compact('data'));
    }

    public function removeCart($id)
    {
        $data['row'] = Cart::find($id);
        if ($data['row']) {
            if ($data['row']->delete()) {
                request()->session()->flash('success', 'Data Deleted Successfully');

            } else {
                request()->session()->flash('error', 'Data Deletion failed');
            }
        } else {
            request()->session()->flash('error', 'Invalid request');
        }
        return redirect()->route('cart.list' );
    }

    public function product(){
        $this->title= 'Product';
        $data = [];
//        $data['cart']=Cart::where('user_id',Auth::user()->id)->count();
        $data['product']=Product::latest()->where('status',1)->take(20)->get();
        $data['category']=Category::where('status',1)->get();
        $data['footer']=Miscellaneous::all();
        return view($this->__loadDataToView($this->view . 'pages.product'),compact('data'));
    }

    public function search(Request $request){
        $this->title= 'Searched Products';
        $data = [];
//        $data['cart']=Cart::where('user_id',Auth::user()->id)->count();

        $query = $request->input('term');
        $data['searched_items'] = Product::where('name', 'like', "%$query%")->where('status',1)
            ->orderBy('name', 'asc')
            ->get();
//        dd($data['searched_items'] );
//        $data['product']=Product::latest()->where('status',1)->take(20)->get();
        $data['category']=Category::where('status',1)->get();
        $data['footer']=Miscellaneous::all();
        return view($this->__loadDataToView($this->view . 'pages.search'),compact('data','query'));
    }
    public function checkout(Request $request ){

        $this->title= 'CheckOut';
        $data = [];
//        $data['cart']=Cart::where('user_id',Auth::user()->id)->count();
        $data['product']=Product::latest()->where('status',1)->take(20)->get();
        $data['category']=Category::where('status',1)->get();
        $data['footer']=Miscellaneous::all();
        $data['cartItems'] = Cart::where('user_id',Auth::user()->id)->get();

        return view($this->__loadDataToView($this->view . 'pages.checkout'),compact('data'));
    }
public function sendConfirmationemail(UserRequest $request){
    $this->title= 'Thank-You';
//    $data['cart']=Cart::where('user_id',Auth::user()->id)->count();
    $data['cartItems'] = Cart::where('user_id',Auth::user()->id)->get();
    foreach ($data['cartItems'] as $carts) {
        $data['order']=Order::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'phone'=>$request->phone,
            'address'=>$request->address,
            'user_id' =>$carts->user_id,
            'product_id' => $carts->product_id,
            "quantity" => $carts->quantity,
            'amount' =>$carts->price,
        ]);
    }
    $data['category']=Category::all();
    $data['footer']=Miscellaneous::all();
    $order_data = [
        'name' => $request->input('name'),
        'email' => $request->input('email'),
        'address' => $request->input('address'),
        'phone' => $request->input('phone'),
    ];
    Mail::to('Admin@gmail.com')->send(new orderConfirmation($order_data));
    return view($this->__loadDataToView($this->view . 'pages.thankyou'),compact('data'));
//    $data['cartItems'] = Cart::where('user_id',Auth::user()->id)->get();//

}
 public function profile(){

     $this->title= 'ProfView';
//     $data['cart']=Cart::where('user_id',Auth::user()->id)->count();
     $data['row'] =User::where('id',(Auth::user()->id))->get();
     $data['category']=Category::where('status',1)->get();
     $data['footer']=Miscellaneous::all();
     return view($this->__loadDataToView($this->view . 'pages.profile_view'),compact('data'));
 }

    public function profile_edit(Request $request){

//        $data['cart']=Cart::where('user_id',Auth::user()->id)->count();
        $data['category']=Category::where('status',1)->get();
        $data['footer']=Miscellaneous::all();
        $data['row'] =User::where('id',(Auth::user()->id))->get();
//        dd($data['row']);
        if(!$data ['row']){
            request()->session()->flash('error','Invalid Request');
            return redirect()->route($this->__loadDataToView($this->route . 'index'));
        }
        if ($data['row']->update($request->all())) {
            $request->session()->flash('success', $this->panel .' Update Successfully');
        } else {
            $request->session()->flash('error', $this->panel .' Update failed');

        }
        return redirect()->route($this->__loadDataToView($this->view . 'pages.profijle'),compact('data'));
    }

    public function category($id){

        $this->title= 'Categories';
        $data = [];
        $data['product'] = Product::where('category_id', $id)->where('status',1)->get();
        $data['cat']=Category::where('id', $id)->pluck('name');
//        $data['cart']=Cart::where('user_id',Auth::user()->id)->count();
        $data['category']=Category::where('status',1)->get();
        $data['footer']=Miscellaneous::all();
        return view($this->__loadDataToView($this->view . 'pages.categories'),compact('data',));
    }






}
