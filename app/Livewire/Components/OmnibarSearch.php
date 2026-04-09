<?php

namespace App\Livewire\Components;

use App\Models\Borrower;
use App\Models\Collateral;
use App\Models\Loan;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OmnibarSearch extends Component
{
    public $query = '';

    public $results = [];

    protected $pages = [
        ['title' => 'Dashboard', 'keywords' => 'dashboard, home, main, stats', 'route' => 'dashboard', 'permission' => 'view_dashboard', 'icon' => 'dashboard'],
        ['title' => 'Status Board', 'keywords' => 'status, board, kanban, tracking', 'route' => 'status-board', 'permission' => 'manage_loans', 'icon' => 'view_kanban'],
        ['title' => 'Loans List', 'keywords' => 'loans, list, all loans, lending', 'route' => 'loan', 'permission' => 'manage_loans', 'icon' => 'monetization_on'],
        ['title' => 'Pending Loans', 'keywords' => 'pending, approvals, new loans', 'route' => 'loans.pending', 'permission' => 'approve_loans', 'icon' => 'pending_actions'],
        ['title' => 'Create Loan', 'keywords' => 'create loan, new loan, apply', 'route' => 'loan.create', 'permission' => 'manage_loans', 'icon' => 'add_circle'],
        ['title' => 'Action Center', 'keywords' => 'actions, tasks, center, alerts', 'route' => 'actions', 'permission' => 'view_dashboard', 'icon' => 'bolt'],
        ['title' => 'Reports', 'keywords' => 'reports, analytics, statements, download', 'route' => 'reports', 'permission' => 'view_reports', 'icon' => 'bar_chart'],
        ['title' => 'Collections', 'keywords' => 'collections, recovery, collector', 'route' => 'collections', 'permission' => 'manage_collections', 'icon' => 'trending_up'],
        ['title' => 'Collection Entry', 'keywords' => 'collection, entry, repayment, add', 'route' => 'collection.entry', 'permission' => 'enter_collections', 'icon' => 'payments'],
        ['title' => 'Savings Entry', 'keywords' => 'savings, entry, deposit, add', 'route' => 'savings.entry', 'permission' => 'enter_savings', 'icon' => 'account_balance_wallet'],
        ['title' => 'KYC Approval', 'keywords' => 'kyc, approval, review, identity', 'route' => 'kyc.approval', 'permission' => 'approve_kyc', 'icon' => 'verified_user'],
        ['title' => 'Loan Approval', 'keywords' => 'loan, approval, review, pending', 'route' => 'loan.approval', 'permission' => 'approve_loans', 'icon' => 'fact_check'],
        ['title' => 'Guarantor Form Builder', 'keywords' => 'guarantor, form, builder, fields', 'route' => 'settings.guarantor-form', 'permission' => 'manage_settings', 'icon' => 'dynamic_form'],
        ['title' => 'Borrower Form Builder', 'keywords' => 'borrower, fields, registration, form', 'route' => 'settings.form-builder', 'permission' => 'manage_settings', 'icon' => 'settings_input_component'],
        ['title' => 'Team Management', 'keywords' => 'staff, users, management, permissions', 'route' => 'settings.team', 'permission' => 'manage_settings', 'icon' => 'group'],
        ['title' => 'General Settings', 'keywords' => 'organization, settings, logo, config', 'route' => 'settings', 'permission' => 'manage_settings', 'icon' => 'settings'],
        ['title' => 'Notification Settings', 'keywords' => 'notifications, alert, push, email', 'route' => 'settings.notifications', 'permission' => 'manage_settings', 'icon' => 'notifications'],
        ['title' => 'Security Settings', 'keywords' => 'password, security, two factor, account', 'route' => 'settings.security', 'permission' => 'manage_settings', 'icon' => 'security'],
        ['title' => 'Roles & Permissions', 'keywords' => 'roles, access, levels, permissions', 'route' => 'settings.roles', 'permission' => 'manage_settings', 'icon' => 'verified_user'],
        ['title' => 'Loan Products', 'keywords' => 'products, interest, plans, loan types', 'route' => 'settings.loan-products', 'permission' => 'manage_settings', 'icon' => 'inventory'],
        ['title' => 'Portfolios', 'keywords' => 'portfolios, groups, collections, management', 'route' => 'settings.portfolios', 'permission' => 'manage_settings', 'icon' => 'folder_shared'],
    ];

    public function updatedQuery()
    {
        if (strlen($this->query) < 2) {
            $this->results = [];

            return;
        }

        $orgId = Auth::user()->organization_id;
        $search = $this->query;
        $prefix = null;

        if (str_contains($search, ':')) {
            $parts = explode(':', $search, 2);
            $prefix = strtolower(trim($parts[0]));
            $search = trim($parts[1]);
        }

        $allResults = collect();

        // 1. Search Pages (only if no prefix or 'page' prefix)
        if (! $prefix || $prefix === 'page' || $prefix === 'setting') {
            $pageResults = collect($this->pages)
                ->filter(function ($page) use ($search) {
                    return str_contains(strtolower($page['title']), strtolower($search)) ||
                           str_contains(strtolower($page['keywords']), strtolower($search));
                })
                ->map(function ($page) {
                    return [
                        'type' => 'page',
                        'title' => $page['title'],
                        'subtitle' => 'Navigation',
                        'link' => route($page['route']),
                        'permission' => $page['permission'],
                        'icon' => $page['icon'],
                    ];
                });
            $allResults = $allResults->concat($pageResults);
        }

        // 2. Search Borrowers
        if (! $prefix || in_array($prefix, ['customer', 'borrower', 'staff'])) {
            $borrowers = Borrower::with(['user', 'loans.loanOfficer'])
                ->where('organization_id', $orgId)
                ->where(function ($q) use ($search, $prefix) {
                    if ($prefix === 'staff') {
                        $q->whereHas('loans.loanOfficer', function ($lq) use ($search) {
                            $lq->where('name', 'like', '%'.$search.'%');
                        });
                    } else {
                        $q->whereHas('user', function ($uq) use ($search) {
                            $uq->where('name', 'like', '%'.$search.'%')
                                ->orWhere('email', 'like', '%'.$search.'%');
                        })
                            ->orWhere('phone', 'like', '%'.$search.'%')
                            ->orWhere('custom_id', 'like', '%'.$search.'%')
                            ->orWhere('bvn', 'like', '%'.$search.'%')
                            ->orWhere('national_identity_number', 'like', '%'.$search.'%');
                    }
                })
                ->take(5)
                ->get()
                ->map(function ($b) {
                    return [
                        'type' => 'borrower',
                        'title' => $b->user->name,
                        'subtitle' => 'Customer | '.$b->phone,
                        'link' => route('borrower.loans', $b->id),
                        'permission' => 'manage_borrowers',
                        'icon' => 'person',
                    ];
                });
            $allResults = $allResults->concat($borrowers);
        }

        // 3. Search Loans (including prefix statuses)
        $statusPrefixes = ['active', 'applied', 'repaid', 'overdue', 'repayment'];
        if (! $prefix || in_array($prefix, array_merge(['loan', 'staff'], $statusPrefixes))) {
            $loans = Loan::where('organization_id', $orgId)
                ->where(function ($q) use ($search, $prefix) {
                    if ($prefix === 'staff') {
                        $q->whereHas('loanOfficer', function ($lq) use ($search) {
                            $lq->where('name', 'like', '%'.$search.'%');
                        });
                    } else {
                        $q->where('loan_number', 'like', '%'.$search.'%')
                            ->orWhere('amount', 'like', '%'.$search.'%')
                            ->orWhereHas('borrower.user', function ($uq) use ($search) {
                                $uq->where('name', 'like', '%'.$search.'%');
                            });
                    }
                })
                ->when($prefix && in_array($prefix, ['active', 'applied', 'repaid', 'overdue']), function ($q) use ($prefix) {
                    return $q->where('status', $prefix);
                })
                ->when($prefix === 'repayment', function ($q) {
                    return $q->whereIn('status', ['active', 'overdue']);
                })
                ->take(5)
                ->get()
                ->map(function ($l) {
                    return [
                        'type' => 'loan',
                        'title' => 'Loan '.$l->loan_number,
                        'subtitle' => strtoupper($l->status).' | ₦'.number_format($l->amount->getMajorAmount()).' | '.$l->borrower->user->name,
                        'link' => route('loan.show', $l->id),
                        'permission' => 'manage_loans',
                        'icon' => 'payments',
                    ];
                });
            $allResults = $allResults->concat($loans);
        }

        // 4. Search Portfolios
        if (! $prefix || $prefix === 'portfolio') {
            $portfolios = \App\Models\Portfolio::where('organization_id', $orgId)
                ->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                })
                ->take(5)
                ->get()
                ->map(function ($p) {
                    return [
                        'type' => 'portfolio',
                        'title' => $p->name,
                        'subtitle' => 'Portfolio | '.$p->borrowers()->count().' Borrowers',
                        'link' => route('settings.portfolios'),
                        'permission' => 'manage_settings',
                        'icon' => 'folder_shared',
                    ];
                });
            $allResults = $allResults->concat($portfolios);
        }

        // 5. Search Collateral
        if (! $prefix || $prefix === 'collateral') {
            $collateral = Collateral::where('organization_id', $orgId)
                ->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                })
                ->take(5)
                ->get()
                ->map(function ($c) {
                    return [
                        'type' => 'collateral',
                        'title' => $c->name,
                        'subtitle' => 'Collateral | Value: ₦'.number_format($c->value->getMajorAmount()),
                        'link' => route('vault'),
                        'permission' => 'manage_vault',
                        'icon' => 'inventory_2',
                    ];
                });
            $allResults = $allResults->concat($collateral);
        }

        $this->results = $allResults->toArray();
    }

    public function navigateTo($url, $permission = null)
    {
        if ($permission && ! Auth::user()->can($permission)) {
            $this->dispatch('custom-alert', [
                'type' => 'error',
                'message' => 'ACCESS DENIED: You do not have the required permissions to access this page.',
            ]);

            return;
        }

        return redirect($url);
    }

    public function render()
    {
        return view('livewire.components.omnibar-search');
    }
}
