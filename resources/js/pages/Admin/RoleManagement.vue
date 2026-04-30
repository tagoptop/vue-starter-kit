<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';

type Role = 'staff' | 'checker';

type RoleUser = {
    id: number;
    name: string;
    email: string;
    role: string;
};

const props = defineProps<{
    users: RoleUser[];
    assignableRoles: Role[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Role management',
                href: '/admin/roles',
            },
        ],
    },
});

const roleForms = new Map<number, ReturnType<typeof useForm<{ role: Role }>>>();

const getRoleForm = (userId: number, role: Role) => {
    if (!roleForms.has(userId)) {
        roleForms.set(userId, useForm<{ role: Role }>({ role }));
    }

    return roleForms.get(userId)!;
};

const updateRole = (user: RoleUser, role: Role) => {
    const form = getRoleForm(user.id, role);

    form.role = role;
    form.patch(`/admin/roles/${user.id}`, {
        preserveScroll: true,
    });
};

const onRoleChange = (user: RoleUser, event: Event) => {
    const selectElement = event.target as HTMLSelectElement;

    updateRole(user, selectElement.value as Role);
};
</script>

<template>
    <Head title="Role management" />

    <div class="space-y-6 p-4">
        <Heading
            variant="small"
            title="Role management"
            description="Admin-only page to assign staff or checker roles."
        />

        <div class="overflow-hidden rounded-xl border">
            <table class="w-full text-sm">
                <thead class="bg-muted/40 text-left">
                    <tr>
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Email</th>
                        <th class="px-4 py-3 font-medium">Current role</th>
                        <th class="px-4 py-3 font-medium">Assign role</th>
                        <th class="px-4 py-3 font-medium">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in props.users" :key="user.id" class="border-t">
                        <td class="px-4 py-3">{{ user.name }}</td>
                        <td class="px-4 py-3">{{ user.email }}</td>
                        <td class="px-4 py-3 capitalize">{{ user.role }}</td>
                        <td class="px-4 py-3">
                            <div v-if="user.role === 'admin'" class="text-xs text-muted-foreground">
                                Not editable here
                            </div>
                            <div v-else>
                                <Label :for="`role-${user.id}`" class="sr-only">Assign role</Label>
                                <select
                                    :id="`role-${user.id}`"
                                    class="h-9 rounded-md border border-input bg-background px-3 py-1 text-sm"
                                    :value="getRoleForm(user.id, (user.role as Role)).role"
                                    @change="onRoleChange(user, $event)"
                                >
                                    <option
                                        v-for="role in props.assignableRoles"
                                        :key="role"
                                        :value="role"
                                    >
                                        {{ role }}
                                    </option>
                                </select>
                                <InputError :message="getRoleForm(user.id, (user.role as Role)).errors.role" class="mt-2" />
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <Button
                                v-if="user.role !== 'admin'"
                                size="sm"
                                :disabled="getRoleForm(user.id, (user.role as Role)).processing"
                                @click="updateRole(user, getRoleForm(user.id, (user.role as Role)).role)"
                            >
                                Save
                            </Button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
