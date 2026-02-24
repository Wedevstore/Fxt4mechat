<?php

namespace App\Repositories;

use App\Models\Plan;
use App\Models\PlanCredential;
use App\Traits\PaymentTrait;

class PlanRepository
{
    use PaymentTrait;

    private $model;

    public function __construct(Plan $model)
    {
        $this->model = $model;
    }


    public function all($data = [])
    {
        return $this->model->orderBy('price')->get();
    }
   
    
    public function findActive($id)
    {
        return $this->model->active()->findOrfail($id);
    }

    public function store($request)
    {
        $request['price'] = priceFormatUpdate($request['price'], setting('default_currency'));
        $request['color'] = $request['color'] ?? '#E0E8F9';
    
        // Handle 'is_free' logic
        if (isset($request['is_free']) && $request['is_free'] == 1) {
            $request['price']   = 0;
            $request['is_free'] = 1;
        } else {
            $request['is_free'] = 0;
        }
    
        // Convert special value for unlimited fields
        $fieldsToCheck = ['contact_limit', 'campaigns_limit', 'conversation_limit', 'max_chatwidget', 'max_flow_builder', 'max_bot_reply', 'team_limit', 'per_device_credit_limit'];
        foreach ($fieldsToCheck as $field) {
            if (isset($request[$field]) && $request[$field] == -1) {
                $request[$field] = -1; // Use -1 to represent unlimited
            }
        }
    
        // Create package
        $package = $this->model->create($request);
        $request['id'] = $package->id;
    
        // Create plans for different payment gateways
        $this->createStripePlan($request);
        $this->createPaypalPlan($request);
        $this->createPaddlePlan($request);
        $this->createRazorPayPlan($request);
    
        return $package;
    }
    

    public function update($request, $id)
    {
        $request['price'] = priceFormatUpdate($request['price'], setting('default_currency'));
        $request['color'] = $request['color'] ?? '#E0E8F9';

        // Handle 'is_free' logic
        if (isset($request['is_free']) && $request['is_free'] == 1) {
            $request['price']   = 0;
            $request['is_free'] = 1;
        } else {
            $request['is_free'] = 0;
        }

        // Convert special value for unlimited fields
        $fieldsToCheck = ['contact_limit', 'campaigns_limit', 'conversation_limit', 'max_chatwidget', 'max_flow_builder', 'max_bot_reply', 'team_limit', 'per_device_credit_limit'];
        foreach ($fieldsToCheck as $field) {
            if (isset($request[$field]) && $request[$field] == -1) {
                $request[$field] = -1; // Use -1 to represent unlimited
            }
        }

        // Find and update package
        $package = $this->model->findOrfail($id);
        $request['id'] = $package->id;
        
        // Create plans for different payment gateways
        $this->createStripePlan($request);
        $this->createPaypalPlan($request);
        $this->createPaddlePlan($request);
        $this->createRazorPayPlan($request);

        $package->update($request);
       
        return $package;
    }

    public function find($id)
    {
        return$this->model->find($id);
    }

    public function status($data)
    {
        $key         =$this->model->findOrfail($data['id']);
        $key->status = $data['status'];

        return $key->save();
    }

    public function destroy($id)
    {
        PlanCredential::where('plan_id', $id)->delete();

        return$this->model->destroy($id);
    }

    public function createStripePlan($data)
    {
        if (arrayCheck('stripe', $data)) {
            $package = PlanCredential::where('plan_id', $data['id'])->where('title', 'stripe')->first();

            if ($package) {
                $package->value = $data['stripe'];
                $package->save();
            } else {
                PlanCredential::create([
                    'plan_id' => $data['id'],
                    'title'   => 'stripe',
                    'value'   => $data['stripe'],
                ]);
            }
        }

        return null;
    }

    public function createPaypalPlan($data)
    {
        if (arrayCheck('paypal', $data)) {
            $package = PlanCredential::where('plan_id', $data['id'])->where('title', 'paypal')->first();

            if ($package) {
                $package->value = $data['paypal'];
                $package->save();
            } else {
                PlanCredential::create([
                    'plan_id' => $data['id'],
                    'title'   => 'paypal',
                    'value'   => $data['paypal'],
                ]);
            }
        }

        return null;
    }

    public function createPaddlePlan($data)
    {
        if (arrayCheck('paddle', $data)) {
            $package = PlanCredential::where('plan_id', $data['id'])->where('title', 'paddle')->first();

            if ($package) {
                $package->value = $data['paddle'];
                $package->save();
            } else {
                PlanCredential::create([
                    'plan_id' => $data['id'],
                    'title'   => 'paddle',
                    'value'   => $data['paddle'],
                ]);
            }
        }

        return null;
    }
    public function createRazorPayPlan($data)
    {
        if (arrayCheck('razor_pay', $data)) {
            $package = PlanCredential::where('plan_id', $data['id'])->where('title', 'razor_pay')->first();
            if ($package) {
                $package->value = $data['razor_pay'];
                $package->save();
            } else {
                PlanCredential::create([
                    'plan_id' => $data['id'],
                    'title'   => 'razor_pay',
                    'value'   => $data['razor_pay'],
                ]);
            }
        }

        return null;
    }

    public function activePlans($data = [], $billing_period = 'all')
    {
        if ($billing_period == 'all') {
            return $this->model->where('status', '1')->orderBy('price', 'ASC')->get();
        } else {
            if ($billing_period == 'daily') {
                return$this->model->where('status', '1')->where('billing_period', 'daily')->orderBy('price', 'ASC')->get();
            } elseif ($billing_period == 'weekly') {
                return$this->model->where('status', '1')->where('billing_period', 'weekly')->orderBy('price', 'ASC')->get();
            } elseif ($billing_period == 'monthly') {
                return$this->model->where('status', '1')->where('billing_period', 'monthly')->orderBy('price', 'ASC')->get();
            } elseif ($billing_period == 'quarterly') {
                return$this->model->where('status', '1')->where('billing_period', 'quarterly')->orderBy('price', 'ASC')->get();
            } elseif ($billing_period == 'half_yearly') {
                return$this->model->where('status', '1')->where('billing_period', 'half_yearly')->orderBy('price', 'ASC')->get();
            } elseif ($billing_period == 'yearly') {
                return$this->model->where('status', '1')->where('billing_period', 'yearly')->orderBy('price', 'ASC')->get();
            } else {
                return$this->model->where('status', '1')->orderBy('price', 'ASC')->get();
            }
        }
    }

    public function getPGCredential($plan_id, $title)
    {
        return PlanCredential::where('plan_id', $plan_id)->where('title', $title)->value('value');
    }

    public function bestSellingPlan()
    {
        return$this->model->withCount('subscriptions')->orderByDesc('subscriptions_count')->where('status', 1)->take(5)->get();
    }
}
