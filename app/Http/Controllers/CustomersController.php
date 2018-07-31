<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Customer;
use App\Package;
use Illuminate\Http\Request;
use Session;
use Auth;
use App\User;
use App\Activation;
use App\Notifications\AccountToBeConfirmed;
use App\Notifications\CreditRecharged;
use App\Subscription;
use Carbon\Carbon;
use DB;

class CustomersController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\View\View
   */
  public function index(Request $request)
  {
      //$customers = Customer::paginate($perPage);
      $customers = Customer::all();
      dd('customers');

      return view('customers.index', compact('customers'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\View\View
   */
  public function create()
  {
      return view('customers.create');
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param \Illuminate\Http\Request $request
   *
   * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
   */
  public function store(Request $request)
  {
      Customer::create($request->all());
      Session::flash('flash_message', trans('messages.added_successfully'));
      return redirect('customers');
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   *
   * @return \Illuminate\View\View
   */
  public function show($id)
  {
      $customer = Customer::findOrFail($id);
      return view('customers.show', compact('customer'));
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   *
   * @return \Illuminate\View\View
   */
  public function edit($id)
  {
      $customer = Customer::findOrFail($id);
      return view('customers.edit', compact('customer'));
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  int  $id
   * @param \Illuminate\Http\Request $request
   *
   * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
   */
  public function update($id, Request $request)
  {
      $customer = Customer::findOrFail($id);
      $customer->update($request->all());
      Session::flash('flash_message', trans('messages.updated_successfully'));
      return redirect('customers');
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   *
   * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
   */
  public function destroy($id)
  {
      Customer::destroy($id);
      Session::flash('flash_message', trans('messages.deleted_successfully'));
      return redirect('customers');
  }

  public function subscribe($option, $artwork_id){
    $packages = Package::orderBy('price', 'desc')->get();
    return view('customers.subscribe', compact('packages', 'artwork_id'));
  }

  public function checkout($package, $artwork_id){
    $package = Package::findOrFail($package);
    if(Auth::check()){
      $user = Auth::user();
    }else{
      $user = new User();
    }
    return view('customers.checkout', compact('package', 'user', 'artwork_id'));
  }

  public function postCheckout(Request $request){
    Session::forget('artwork_id');
    Session::forget('subscription_id');
    /**** step1: ****/
    //create user if not already existing
    $this->validate($request,[
      'name' => 'required|string|max:255',
      'email' => 'required|string|email|max:255',
      'mobile' => 'required|digits:10',
      'city' => 'required',
    ]);

    if(\Auth::check()){
      $user = \Auth::user();
    }else{
      // notify user with new account created
      $user = $this->createUser($request->all());
      $activation = Activation::createFromUser($user);
      $user->notify(new AccountToBeConfirmed($activation->code));
    }

    $subscription = $this->createSubscription($request->all(), $user->id);
    /**** step3: ****/
    // if online payment selected create pay page
    // authentication
    // $email = 'kas@osloob.com.sa';
    // $secret_key = '3sfVhpMBzWiTceRvo4DavsvxmO6E7Gda8t6pSS0rPjk2ycxXha7MCDkYrqDz6RJfkQEdIFFyY0Axc8utiBgVfAUG1xf8oZnJP1Wy';
    $email = 'kosalduong0518@gmail.com';
    $secret_key = 'c3USZEjHtPUJj37XaEV8IC0Vwg1Jk4zx8tp88IPBxKlROsOY7K7Pm7LSnNBISbf1FE9NnYpUJKMxdAvklDaHKVA8IcQBn6C2KLQ7';

    if ($subscription['payment_method'] == 'paytabs') {
      $obj = json_decode($this->runPost("https://www.paytabs.com/apiv2/validate_secret_key", array("merchant_email"=> $email, "secret_key"=>  $secret_key)));
      if($obj->response_code == "4000"){
        // DB::table('subscriptions')
        //     ->where('id', $subscription['id'])
        //     ->update(['status' => 1]);

        // Create Pay Page
        $result = $this->create_pay_page(array(
            //Customer's Personal Information
            
            'cc_first_name' => "Khaled",          //This will be prefilled as Credit Card First Name
            'cc_last_name' => "Alshehri",            //This will be prefilled as Credit Card Last Name
            'cc_phone_number' => "00966",
            'phone_number' => "33333333",
            'email' => "customer@gmail.com",
            
            //Customer's Billing Address (All fields are mandatory)kas@osloob.com.sa
            //When the country is selected as USA or CANADA, the state field should contain a String of 2 characters containing the ISO state code otherwise the payments may be rejected. 
            //For other countries, the state can be a string of up to 32 characters.
            'billing_address' => "manama bahrain",
            'city' => "manama",
            'state' => "manama",
            'postal_code' => "00966",
            'country' => "SAU",
            
            //Customer's Shipping Address (All fields are mandatory)
            'address_shipping' => "Juffair bahrain",
            'city_shipping' => "manama",
            'state_shipping' => "manama",
            'postal_code_shipping' => "00966",
            'country_shipping' => "SAU",
           
           //Product Information
            "products_per_title" => "Image",   //Product title of the product. If multiple products then add “||” separator
            'quantity' => "1",                                    //Quantity of products. If multiple products then add “||” separator
            'unit_price' => $subscription['payment_amount'],                                  //Unit price of the product. If multiple products then add “||” separator.
            "other_charges" => "0",                                     //Additional charges. e.g.: shipping charges, taxes, VAT, etc.
            
            'amount' => $subscription['payment_amount'],                                          //Amount of the products and other charges, it should be equal to: amount = (sum of all products’ (unit_price * quantity)) + other_charges
            'discount'=>"0",                                                //Discount of the transaction. The Total amount of the invoice will be= amount - discount
            'currency' => "SAR",                                            //Currency of the amount stated. 3 character ISO currency code 
           

            
            //Invoice Information
            'title' => "Khaled Alshehri",               // Customer's Name on the invoice
            "msg_lang" => "en",                 //Language of the PayPage to be created. Invalid or blank entries will default to English.(Englsh/Arabic)
            "reference_no" => rand(),        //Invoice reference number in your system
           
            
            //Website Information
            "site_url" => "https://dev.yohkaa.com",      //The requesting website be exactly the same as the website/URL associated with your PayTabs Merchant Account
            // "site_url" => "http://www.osloob.com.sa/",      //The requesting website be exactly the same as the website/URL associated with your PayTabs Merchant Account
            'return_url' => "https://dev.yohkaa.com/paycomplete",
            // 'return_url' => "http://18.185.74.238/paycomplete",
            "cms_with_version" => "API USING PHP",

            "paypage_info" => "1"
        ), $email, $secret_key);
        if ($result->response_code == '4012') {
          /**** step2: ****/
          // create order
          Session::push('artwork_id', $request['artwork_id']);
          Session::push('subscription_id', $subscription['id']);

          return redirect($result->payment_url);
        } else {
          Session::flash('error', trans($result->result));
          return redirect()->back();
        }
        $user->notify(new CreditRecharged($subscription));
      } else {
          Session::flash('error', trans('Your subscription was failed!'));
          return redirect()->back();
      }
    } else {
        $user->notify(new CreditRecharged($subscription));
        Session::flash('success', trans('messages.subscription_created'));
        return redirect()->back();
    }
    // redirect user to Paytabs
  }

  public function verify(request $request) {
    var_dump($request);exit;
    print_r("1: ".$request);
      $artwork_id = Session::get('artwork_id');
      $subscription_id = Session::get('subscription_id');
      $sub = Subscription::findOrFail($subscription_id);

      $sub->payment_date = date('Y-m-d H:i:s');
      $sub->payment_reference = $request->reference_no;
      $sub->payment_amount = $request->amount;
      // add credit to the user
      if($sub->user->credit){
        $sub->user->credit->current_credit =  $sub->user->credit->current_credit + $sub->package->credit;
      }else{
        $sub->user->intiateCreditRecord($sub);
      }
      switch ($request->response_code) {
        case 100:
          $sub->status = \Config::get('constants.subscription_status.PAID');
          break;
        case 481:
          break;
        case 482:
          break;
        default:
          $sub->status = \Config::get('constants.subscription_status.CANCELLED');
          break;
      }
      $sub->save();
    print_r(" 2: ----------------");


      $user = Auth::user();
      $artwork = Artwork::findOrFail($artwork_id);
      DB::beginTransaction();
      try {
          $credit_before = $user->credit->current_credit;
          // deduct the credit for customer
          $user->credit->current_credit = $user->credit->current_credit - 1;
          $user->credit->save();
          // increase balance for contributor
          $artwork->contributor->balance = $artwork->contributor->balance + 1;
          $artwork->contributor->save();
          // create the download transaction
          $download = Download::create([
            'artwork_id' => $artwork->id,
            'user_id' => $user->id,
            'downloaded_at' => Carbon::now(),
            'browser' => '',
            'ip_address' => '',
            'credit_before' => $credit_before,
            'credit_after' => $user->credit->current_credit,
          ]);

          DB::commit();
          // notify
          $sub->user->notify(new CreditRecharged($sub));
          $downloads = Download::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
          return view('customers.dashboard' , compact('downloads'));
       }
       catch (Exception $e) {
           DB::rollback();
           $downloads = Download::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
           return view('customers.dashboard' , compact('downloads'));
       }
  }

  protected function createUser(array $data)
  {
      return User::create([
          'name' => $data['name'],
          'email' => $data['email'],
          'mobile' => $data['mobile'],
          'city' => $data['city'],
          'password' => bcrypt('123456'),
      ]);
  }

  protected function createSubscription(array $data, $user_id)
  {
      $pckg = Package::findOrFail($data['package_id']);

      return Subscription::create([
          'package_id' => $data['package_id'],
          'user_id' => $user_id,
          'expiry_date' => Carbon::now()->addDays($pckg->validity),
          'status' => \Config::get('constants.subscription_status.PENDING'),
          'payment_method' => $data['payment_method'],
          'payment_date' => null,
          'payment_reference' => null,
          'payment_amount' => $pckg->price,
      ]);
  }

  protected function create_pay_page($values, $email, $secret_key) {
    $values['merchant_email'] = $email;
    $values['secret_key'] = $secret_key;
    $values['ip_customer'] = $_SERVER['REMOTE_ADDR'];
    $values['ip_merchant'] = $_SERVER['SERVER_ADDR'];
    // $values['ip_merchant'] = '151.101.1.105';
    return json_decode($this->runPost("https://www.paytabs.com/apiv2/create_pay_page", $values));
  }

  protected function verify_payment($payment_reference, $email, $secret_key){
    $values['merchant_email'] = $email;
    $values['secret_key'] = $secret_key;
    $values['payment_reference'] = $payment_reference;
    return json_decode($this->runPost("https://www.paytabs.com/apiv2/verify_payment", $values));
    }

  protected function runPost($url, $fields) {
      $fields_string = "";
      foreach ($fields as $key => $value) {
          $fields_string .= $key . '=' . $value . '&';
      }
      $fields_string = rtrim($fields_string, '&');
      $ch = curl_init();
      $ip = $_SERVER['REMOTE_ADDR'];

      $ip_address = array(
          "REMOTE_ADDR" => $ip,
          "HTTP_X_FORWARDED_FOR" => $ip
      );
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $ip_address);
      curl_setopt($ch, CURLOPT_POST, count($fields));
      curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_REFERER, 1);

      $result = curl_exec($ch);
      curl_close($ch);
      
      return $result;
  }
}
