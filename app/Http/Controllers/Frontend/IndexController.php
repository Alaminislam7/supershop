<?php

namespace App\Http\Controllers\Frontend;

use Auth;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MultiImg;
use App\Models\Product;
use App\Models\Slider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class IndexController extends Controller
{
	public function index()
	{
		$products = Product::where('status', 1)->orderBy('id', 'DESC')->limit(6)->get();
		$sliders = Slider::where('status', 1)->orderBy('id', 'DESC')->limit(3)->get();
		$categories = Category::orderBy('category_name_en', 'ASC')->get();
		return view('frontend.index', compact('categories', 'sliders', 'products'));
	}

	public function UserLogout()
	{
		Auth::logout();
		return Redirect()->route('login');
	}


	public function UserProfile()
	{
		$id = Auth::user()->id;
		$user = User::find($id);
		return view('frontend.profile.user_profile', compact('user'));
	}

	public function UserProfileStore(Request $request)
	{
		$data = User::find(Auth::user()->id);
		$data->name = $request->name;
		$data->email = $request->email;
		$data->phone = $request->phone;


		if ($request->file('profile_photo_path')) {
			$file = $request->file('profile_photo_path');
			@unlink(public_path('upload/user_images/' . $data->profile_photo_path));
			$filename = date('YmdHi') . $file->getClientOriginalName();
			$file->move(public_path('upload/user_images'), $filename);
			$data['profile_photo_path'] = $filename;
		}
		$data->save();

		$notification = array(
			'message' => 'User Profile Updated Successfully',
			'alert-type' => 'success'
		);

		return redirect()->route('dashboard')->with($notification);
	} // end method 


	public function UserChangePassword()
	{
		$id = Auth::user()->id;
		$user = User::find($id);
		return view('frontend.profile.change_password', compact('user'));
	}


	public function UserPasswordUpdate(Request $request)
	{

		$validateData = $request->validate([
			'oldpassword' => 'required',
			'password' => 'required|confirmed',
		]);

		$hashedPassword = Auth::user()->password;
		if (Hash::check($request->oldpassword, $hashedPassword)) {
			$user = User::find(Auth::id());
			$user->password = Hash::make($request->password);
			$user->save();
			Auth::logout();
			return redirect()->route('user.logout');
		} else {
			return redirect()->back();
		}
	} // end method

	public function ProductDetails($id, $slug)
	{
		$product = Product::findOrFail($id);

		$color_en = $product->product_color_en;
		$product_color_en = explode(',', $color_en);

		$color_hin = $product->product_color_hin;
		$product_color_hin = explode(',', $color_hin);

		$size_en = $product->product_size_en;
		$product_size_en = explode(',', $size_en);

		$size_hin = $product->product_size_hin;
		$product_size_hin = explode(',', $size_hin);

		$multiImag = MultiImg::where('product_id', $id)->get();

		$cat_id = $product->category_id;
		$relatedProduct = Product::where('category_id', $cat_id)->where('id', '!=', $id)->orderBy('id', 'DESC')->get();
		return view('frontend.product.product_details', compact('product', 'multiImag', 'product_color_en', 'product_color_hin', 'product_size_en', 'product_size_hin', 'relatedProduct'));
	}
}
