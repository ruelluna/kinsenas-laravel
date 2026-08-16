<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Models\ContentPost;

trait AuthorizesContentPost
{
    protected function authorizeContentPostAccess(?ContentPost $post = null): bool
    {
        $user = $this->user();

        if ($user === null || ! $user->canManageContent()) {
            return false;
        }

        if ($post === null) {
            return true;
        }

        return $user->canManageContentPost($post);
    }
}
