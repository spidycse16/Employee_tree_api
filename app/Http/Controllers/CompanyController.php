<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Contract;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function getManagerTree($id)
    {
        $company = Company::find($id);
        if (!$company) {
            return response()->json(['error' => 'Company not found'], 404);
        }

        $managerTree = $this->buildManagerTree($id);
        return response()->json([
            'company_name' => $company->name,
            'manager_tree' => $managerTree
        ]);
    }

    public function buildManagerTree($companyId, $managerId = null)
    {
        $tree = [];

        $contracts = Contract::where('company_id', $companyId)
                             ->where(function($query) use ($managerId) {
                                 if (is_null($managerId)) {
                                     $query->whereNull('manager_id');
                                 } else {
                                     $query->where('manager_id', $managerId);
                                 }
                             })
                             ->with('employee')
                             ->get();

        foreach ($contracts as $contract) {
            $subordinates = $this->buildManagerTree($companyId, $contract->employee_id);
            $tree[] = [
                'employee_id' => $contract->employee_id,
                'employee_name' => $contract->employee->name,
                'subordinates' => $subordinates
            ];
        }

        return $tree;
    }


    public function getAllCompanies()
    {
        $companies = Company::all();
        $result = [];

        foreach ($companies as $company) {
            $managerTree = $this->buildManagerTree($company->id);
            $result[] = [
                'company_name' => $company->name,
                'manager_tree' => $managerTree,

            ];
        }
        return response()->json($result);
    }

    public function getEmployeeTree($companyId, $employeeId)
    {
        $company = Company::find($companyId);

        if (!$company) {
            return response()->json(['error' => 'Company not found'], 404);
        }
        $employee = Contract::where('company_id', $companyId)
                            ->where('employee_id', $employeeId)
                            ->with('employee')
                            ->first();

        if (!$employee) {
            return response()->json(['error' => 'Employee not found in the specified company'], 404);
        }

        $managerTree = $this->buildManagerTree($companyId, $employeeId);

        return response()->json([
            'company_name' => $company->name,
            'employee_name' => $employee->employee->name,
            'manager_tree' => $managerTree
        ]);
    }
    
}



