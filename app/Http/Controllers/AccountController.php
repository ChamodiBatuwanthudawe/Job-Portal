<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    // This method will show user registration page
    public function registration()
    {
        return view('front.account.registration');
    }

    // This method will save a user
    public function processRegistration(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:5|same:confirm_password',
            'confirm_password' => 'required',
        ]);

        if($validator->passes()) {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->save();

            session()->flash('success', 'You have registered successfully.');
            return response()->json([
                'status' => true,
                'errors' => []
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    // This method will show user login page
    public function login()
    {
        return view('front.account.login');
    }

    public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if($validator->passes()) {
            if(Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                return redirect()->route('account.profile');
            } else {
                return redirect()->route('account.login')->with('error', 'Either email or password is incorrect');
            }
        } else {
            return redirect()->route('account.login')
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }
    }

    public function profile()
    {
        $id = Auth::user()->id;
        $user = User::where('id', $id)->first();
        
        return view('front.account.profile', [
            'user' => $user
        ]);
    }

    public function updateProfile(Request $request)
    {
        $id = Auth::user()->id;
        $validator = Validator::make($request->all(),[
            'name' => 'required|min:5|max:20',
            'email' => 'required|email|unique:users,email,'.$id.',id'
        ]);

        if($validator->passes()) {
            $user = User::find($id);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->designation = $request->designation;
            $user->mobile = $request->mobile;
            $user->save();

            session()->flash('success', 'Profile updated successfully.');
            return response()->json([
                'status' => true,
                'errors' => []
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('account.login');
    }

    public function updateProfilePic(Request $request)
    {
        $id = Auth::user()->id;
        $validator = Validator::make($request->all(),[
            'image' => 'required|image'
        ]);

        if($validator->passes()) {
            $image = $request->image;
            $ext = $image->getClientOriginalExtension();
            $imageName = $id.'-'.time().'.'.$ext;
            $image->move(public_path('/profile_pic/'), $imageName);

            User::where('id', $id)->update(['image' => $imageName]);

            session()->flash('success', 'Profile picture updated successfully.');
            return response()->json([
                'status' => true,
                'errors' => []
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function createJob()
    {
        $categories = Category::orderBy('name', 'ASC')->where('status', 1)->get();
        $jobTypes = JobType::orderBy('name', 'ASC')->where('status', 1)->get();

        return view('front.account.job.create', [
            'categories' => $categories,
            'jobTypes' => $jobTypes,
        ]);
    }

   public function saveJob(Request $request)
{
    $rules = [
        'title' => 'required|min:5|max:200',
        'category' => 'required',
        'jobType' => 'required',
        'vacancy' => 'required|integer',
        'location' => 'required|max:50',
        'description' => 'required',
        'company_name' => 'required|min:3|max:75',
        'experience' => 'required',
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->passes()) {
        $job = new Job();
        $job->title = $request->title;
        $job->category_id = $request->category;
        $job->job_type_id = $request->jobType;
        $job->user_id = Auth::user()->id;
        $job->vacancy = $request->vacancy;
        $job->salary = $request->salary;
        $job->location = $request->location;
        $job->description = $request->description;
        $job->benefits = $request->benefits;
        $job->responsibility = $request->responsibility;
        $job->qualifications = $request->qualifications;
        $job->keywords = $request->keywords;
        $job->experience = $request->experience;
        $job->company_name = $request->company_name;
        $job->company_location = $request->company_location;
        $job->company_website = $request->website;
        $job->save();

        session()->flash('success','Job Added Successfully.');
        return response()->json([
            'status' => true,
            'redirect' => route('account.myJobs')
        ]);
    } else {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors()
        ]);
    }
}
public function myJobs()
{
    $jobs = Job::where('user_id',Auth::user()->id)->with('jobType')->orderBy('created_at','DESC')->paginate(10);
    
   return view ('front.account.job.my-jobs',[
    'jobs'=> $jobs 
   ]);
   
}

public function editJob(Request $request, $id){

    $categories = Category::orderBy('name', 'ASC')->where('status', 1)->get();
    $jobTypes = JobType::orderBy('name', 'ASC')->where('status', 1)->get();
    $job = Job::where([
        'user_id' => Auth::user()->id,
        'id' => $id
    ])->first();

    if($job == null){
        abort(404);
    }
    return view('front.account.job.edit',[
        'categories' => $categories,
        'jobTypes' => $jobTypes,
        'job' => $job,
    ]);
}
public function updateJob(Request $request, $id)
{
    // Validation rules
    $rules = [
        'title' => 'required|min:5|max:200',
        'category' => 'required',
        'jobType' => 'required',
        'vacancy' => 'required|integer',
        'location' => 'required|max:50',
        'description' => 'required',
        'company_name' => 'required|min:3|max:75',
        'experience' => 'required',
    ];

    // Validate the request data
    $validator = Validator::make($request->all(), $rules);

    if ($validator->passes()) {
        // Check if the job type exists in the job_types table
        $jobType = JobType::find($request->jobType);

        if (!$jobType) {
            return response()->json([
                'status' => false,
                'errors' => ['jobType' => 'Invalid Job Type selected.']
            ]);
        }

        // Find the job and update
        $job = Job::find($id);
        if (!$job) {
            return response()->json([
                'status' => false,
                'errors' => ['job' => 'Job not found.']
            ]);
        }

        $job->title = $request->title;
        $job->category_id = $request->category;
        $job->job_type_id = $request->jobType;  // Update job type id
        $job->user_id = Auth::user()->id;
        $job->vacancy = $request->vacancy;
        $job->salary = $request->salary;
        $job->location = $request->location;
        $job->description = $request->description;
        $job->benefits = $request->benefits;
        $job->responsibility = $request->responsibility;
        $job->qualifications = $request->qualifications;
        $job->keywords = $request->keywords;
        $job->experience = $request->experience;
        $job->company_name = $request->company_name;
        $job->company_location = $request->company_location;
        $job->company_website = $request->website;
        $job->save();

        // Success response
        session()->flash('success', 'Job Updated Successfully.');
        return response()->json([
            'status' => true,
            'redirect' => route('account.myJobs')
        ]);
    } else {
        // Validation failed, return errors
        return response()->json([
            'status' => false,
            'errors' => $validator->errors()
        ]);
    }
}

public function deleteJob($jobId)
{
    $job = Job::where([
        'user_id' => Auth::user()->id,
        'id' => $jobId
    ])->first();

    if ($job == null) {
        return response()->json([
            'status' => false,
            'message' => 'Either job deleted or not found.'
        ]);
    }

    $job->delete();

    return response()->json([
        'status' => true,
        'message' => 'Job deleted successfully.'
    ]);
}

}


