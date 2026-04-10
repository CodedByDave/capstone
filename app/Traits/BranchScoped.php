<?php

namespace App\Traits;

use App\Models\Employee;

trait BranchScoped
{
    /**
     * Returns the branch name for the authenticated staff user,
     * or null for owners (who can see all branches).
     */
    protected function getStaffBranch(): ?string
    {
        $user = auth()->user();

        if ($user->role === 'owner') {
            return null;
        }

        return Employee::where('user_id', $user->id)->value('branch_name');
    }

    /**
     * Applies a branch filter to a query if the user is staff.
     * Owners see everything; staff see only their branch.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $column  The column to filter on (default: branch_name)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeBranch($query, string $column = 'branch_name')
    {
        $branch = $this->getStaffBranch();

        if ($branch !== null) {
            $query->where($column, $branch);
        }

        return $query;
    }
}
