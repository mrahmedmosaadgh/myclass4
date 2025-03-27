<template>
  <AppLayout title="Permissions Dashboard">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Permissions & User Management Dashboard
        </h2>
        <button
          @click="exportUsers"
          class="px-4 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 flex items-center gap-2"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          Export Users
        </button>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
          <!-- Tabs -->
          <div class="border-b border-gray-200 mb-5">
            <nav class="-mb-px flex space-x-8">
              <button
                v-for="tab in tabs"
                :key="tab.id"
                @click="activeTab = tab.id"
                :class="[
                  activeTab === tab.id
                    ? 'border-indigo-500 text-indigo-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                ]"
              >
                {{ tab.name }}
              </button>
            </nav>
          </div>

          <!-- Users Management Tab -->
          <div v-if="activeTab === 'users-management'" class="space-y-6">
            <div class="flex justify-between items-center">
              <h3 class="text-lg font-medium">User Management</h3>
              <button
                @click="openUserModal()"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
              >
                Create User
              </button>
            </div>

            <!-- Search and Filters -->
            <div class="flex gap-4 mb-4">
              <input
                type="text"
                v-model="userSearch"
                placeholder="Search users..."
                class="flex-1 rounded-md border-gray-300"
              />
              <select 
                v-model="userFilter"
                class="rounded-md border-gray-300"
              >
                <option value="all">All Users</option>
                <option value="active">Active</option>
                <option value="deleted">Deleted</option>
              </select>
            </div>

            <!-- Users Table -->
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roles</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="user in filteredUsers" :key="user.id">
                    <td class="px-6 py-4 whitespace-nowrap">{{ user.name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ user.email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span 
                        v-for="role in user.roles" 
                        :key="role.id"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2"
                      >
                        {{ role.name }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="[
                        user.deleted_at 
                          ? 'bg-red-100 text-red-800' 
                          : 'bg-green-100 text-green-800',
                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full'
                      ]">
                        {{ user.deleted_at ? 'Deleted' : 'Active' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                      <div class="flex space-x-2">
                        <template v-if="!user.deleted_at">
                          <button
                            @click="openUserModal(user)"
                            class="text-indigo-600 hover:text-indigo-900"
                          >
                            Edit
                          </button>
                          <button
                            @click="confirmDeleteUser(user)"
                            class="text-red-600 hover:text-red-900"
                          >
                            Delete
                          </button>
                        </template>
                        <button
                          v-else
                          @click="restoreUser(user)"
                          class="text-green-600 hover:text-green-900"
                        >
                          Restore
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Roles Tab Content -->
          <div v-if="activeTab === 'roles'" class="space-y-6">
            <div class="flex justify-between items-center">
              <h3 class="text-lg font-medium">Roles Management</h3>
              <button
                @click="openRoleModal()"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
              >
                Create Role
              </button>
            </div>

            <div class="bg-white shadow overflow-hidden sm:rounded-md">
              <ul class="divide-y divide-gray-200">
                <li v-for="role in roles" :key="role.id" class="px-4 py-4 sm:px-6">
                  <div class="flex justify-between items-center">
                    <div>
                      <p class="text-sm font-medium text-indigo-600">{{ role.name }}</p>
                      <p class="mt-1 text-xs text-gray-500">Guard: {{ role.guard_name }}</p>
                      <div class="mt-2">
                        <span 
                          v-for="permission in role.permissions" 
                          :key="permission.id"
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 mr-2 mb-1"
                        >
                          {{ permission.name }}
                        </span>
                      </div>
                    </div>
                    <div class="flex space-x-2">
                      <button
                        @click="openRoleModal(role)"
                        class="text-indigo-600 hover:text-indigo-900"
                      >
                        Edit
                      </button>
                      <button
                        @click="deleteRole(role)"
                        class="text-red-600 hover:text-red-900"
                      >
                        Delete
                      </button>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
          </div>

          <!-- Permissions Tab Content -->
          <div v-if="activeTab === 'permissions'">
            <div class="flex justify-between items-center mb-5">
              <h3 class="text-lg font-medium">Manage Permissions</h3>
              <button
                @click="openPermissionModal()"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition"
              >
                Create Permission
              </button>
            </div>

            <!-- Permissions List -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
              <ul class="divide-y divide-gray-200">
                <li v-for="permission in permissions" :key="permission.id">
                  <div class="px-4 py-4 sm:px-6 flex justify-between items-center">
                    <div>
                      <p class="text-sm font-medium text-indigo-600 truncate">
                        {{ permission.name }}
                      </p>
                    </div>
                    <div class="flex space-x-2">
                      <button
                        @click="openPermissionModal(permission)"
                        class="text-indigo-600 hover:text-indigo-900 text-sm"
                      >
                        Edit
                      </button>
                      <button
                        @click="deletePermission(permission)"
                        class="text-red-600 hover:text-red-900 text-sm"
                      >
                        Delete
                      </button>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
          </div>

          <!-- Users Tab Content -->
          <div v-if="activeTab === 'users'">
            <div class="mb-5">
              <h3 class="text-lg font-medium mb-3">Assign Roles to Users</h3>
              
              <!-- Search Input -->
              <div class="mb-4">
                <label for="search" class="block text-sm font-medium text-gray-700">Search Users</label>
                <input
                  type="text"
                  name="search"
                  id="search"
                  v-model="searchQuery"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  placeholder="Enter name or email"
                />
              </div>
              
              <!-- Users List -->
              <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                  <li v-for="user in filteredUsers" :key="user.id">
                    <div class="px-4 py-4 sm:px-6">
                      <div class="flex items-center justify-between">
                        <div>
                          <p class="text-sm font-medium text-indigo-600">
                            {{ user.name }}
                          </p>
                          <p class="text-sm text-gray-500">
                            {{ user.email }}
                          </p>
                        </div>
                        <div>
                          <button
                            @click="openUserRolesModal(user)"
                            class="inline-flex items-center px-3 py-1.5 border border-indigo-600 text-xs font-medium rounded text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none"
                          >
                            Manage Roles
                          </button>
                        </div>
                      </div>
                      <div class="mt-2 text-xs text-gray-500">
                        <span class="font-semibold">Current Roles:</span>
                        <span v-if="user.roles.length">
                          {{ user.roles.map(r => r.name).join(', ') }}
                        </span>
                        <span v-else>No roles assigned</span>
                      </div>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Role Modal -->
    <DialogModal :show="roleModalOpen" @close="roleModalOpen = false">
      <template #title>
        {{ editingRole ? 'Edit Role' : 'Create Role' }}
      </template>

      <template #content>
        <div>
          <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700">Role Name</label>
            <input
              type="text"
              name="name"
              id="name"
              v-model="roleForm.name"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
            <div class="max-h-60 overflow-y-auto p-2 border rounded-md">
              <div v-for="permission in permissions" :key="permission.id" class="flex items-start mb-2">
                <div class="flex items-center h-5">
                  <input
                    :id="`permission-${permission.id}`"
                    type="checkbox"
                    :value="permission.id"
                    v-model="roleForm.permissionIds"
                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                  />
                </div>
                <div class="ml-3 text-sm">
                  <label :for="`permission-${permission.id}`" class="font-medium text-gray-700">{{ permission.name }}</label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>

      <template #footer>
        <SecondaryButton @click="roleModalOpen = false">
          Cancel
        </SecondaryButton>

        <PrimaryButton
          class="ml-3"
          @click="saveRole"
        >
          {{ editingRole ? 'Update' : 'Create' }}
        </PrimaryButton>
      </template>
    </DialogModal>

    <!-- Permission Modal -->
    <DialogModal :show="permissionModalOpen" @close="permissionModalOpen = false">
      <template #title>
        {{ editingPermission ? 'Edit Permission' : 'Create Permission' }}
      </template>

      <template #content>
        <div>
          <div class="mb-4">
            <label for="permission-name" class="block text-sm font-medium text-gray-700">Permission Name</label>
            <input
              type="text"
              name="permission-name"
              id="permission-name"
              v-model="permissionForm.name"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>
        </div>
      </template>

      <template #footer>
        <SecondaryButton @click="permissionModalOpen = false">
          Cancel
        </SecondaryButton>

        <PrimaryButton
          class="ml-3"
          @click="savePermission"
        >
          {{ editingPermission ? 'Update' : 'Create' }}
        </PrimaryButton>
      </template>
    </DialogModal>

    <!-- User Roles Modal -->
    <DialogModal :show="userRolesModalOpen" @close="userRolesModalOpen = false">
      <template #title>
        Manage Roles for {{ selectedUser ? selectedUser.name : '' }}
      </template>

      <template #content>
        <div>
          <div class="mb-4">
            <div class="max-h-60 overflow-y-auto p-2 border rounded-md">
              <div v-for="role in roles" :key="role.id" class="flex items-start mb-2">
                <div class="flex items-center h-5">
                  <input
                    :id="`user-role-${role.id}`"
                    type="checkbox"
                    :value="role.id"
                    v-model="userRolesForm.roleIds"
                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                  />
                </div>
                <div class="ml-3 text-sm">
                  <label :for="`user-role-${role.id}`" class="font-medium text-gray-700">{{ role.name }}</label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>

      <template #footer>
        <SecondaryButton @click="userRolesModalOpen = false">
          Cancel
        </SecondaryButton>

        <PrimaryButton
          class="ml-3"
          @click="saveUserRoles"
        >
          Save Roles
        </PrimaryButton>
      </template>
    </DialogModal>

    <!-- User Modal -->
    <DialogModal :show="userModalOpen" @close="closeUserModal">
      <template #title>
        {{ editingUser ? 'Edit User' : 'Create User' }}
      </template>
      <template #content>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Name</label>
            <input
              type="text"
              v-model="userForm.name"
              class="mt-1 block w-full rounded-md border-gray-300"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input
              type="email"
              v-model="userForm.email"
              class="mt-1 block w-full rounded-md border-gray-300"
            />
          </div>
          <div v-if="!editingUser">
            <label class="block text-sm font-medium text-gray-700">Password</label>
            <input
              type="password"
              v-model="userForm.password"
              class="mt-1 block w-full rounded-md border-gray-300"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Roles</label>
            <select
              multiple
              v-model="userForm.roles"
              class="mt-1 block w-full rounded-md border-gray-300"
            >
              <option v-for="role in roles" :key="role.id" :value="role.id">
                {{ role.name }}
              </option>
            </select>
          </div>
        </div>
      </template>
      <template #footer>
        <button
          @click="closeUserModal"
          class="mr-3 px-4 py-2 text-gray-700 hover:text-gray-900"
        >
          Cancel
        </button>
        <button
          @click="saveUser"
          class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
        >
          {{ editingUser ? 'Update' : 'Create' }}
        </button>
      </template>
    </DialogModal>

    <!-- Delete Confirmation Modal -->
    <DialogModal :show="deleteModalOpen" @close="closeDeleteModal">
      <template #title>
        Delete User
      </template>
      <template #content>
        Are you sure you want to delete this user? This action can be reversed later.
      </template>
      <template #footer>
        <button
          @click="closeDeleteModal"
          class="mr-3 px-4 py-2 text-gray-700 hover:text-gray-900"
        >
          Cancel
        </button>
        <button
          @click="deleteUser"
          class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
        >
          Delete
        </button>
      </template>
    </DialogModal>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DialogModal from '@/Components/DialogModal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { useForm } from '@inertiajs/vue3';

// Props from the controller
const props = defineProps({
  roles: Array,
  permissions: Array,
  users: Array
});

// Tab state
const activeTab = ref('users-management');
const userSearch = ref('');
const userFilter = ref('all');
const userModalOpen = ref(false);
const deleteModalOpen = ref(false);
const editingUser = ref(null);
const userToDelete = ref(null);

const tabs = [
  { id: 'users-management', name: 'Users Management' },
  { id: 'roles', name: 'Roles' },
  { id: 'permissions', name: 'Permissions' },
  { id: 'users', name: 'Users & Roles' }
];

const userForm = useForm({
  name: '',
  email: '',
  password: '',
  roles: []
});

// Computed properties
const filteredUsers = computed(() => {
  let filtered = [...props.users];
  
  // Apply search
  if (userSearch.value) {
    const search = userSearch.value.toLowerCase();
    filtered = filtered.filter(user => 
      user.name.toLowerCase().includes(search) || 
      user.email.toLowerCase().includes(search)
    );
  }
  
  // Apply filter
  if (userFilter.value === 'active') {
    filtered = filtered.filter(user => !user.deleted_at);
  } else if (userFilter.value === 'deleted') {
    filtered = filtered.filter(user => user.deleted_at);
  }
  
  return filtered;
});

// Modal handlers
function openRoleModal(role = null) {
  if (role) {
    editingRole.value = role;
    roleForm.value.name = role.name;
    roleForm.value.permissionIds = role.permissions.map(p => p.id);
  } else {
    editingRole.value = null;
    roleForm.value.name = '';
    roleForm.value.permissionIds = [];
  }
  roleModalOpen.value = true;
}

function openPermissionModal(permission = null) {
  if (permission) {
    editingPermission.value = permission;
    permissionForm.value.name = permission.name;
  } else {
    editingPermission.value = null;
    permissionForm.value.name = '';
  }
  permissionModalOpen.value = true;
}

function openUserRolesModal(user) {
  selectedUser.value = user;
  userRolesForm.value.userId = user.id;
  userRolesForm.value.roleIds = user.roles.map(r => r.id);
  userRolesModalOpen.value = true;
}

// CRUD operations
function saveRole() {
  if (editingRole.value) {
    router.put(`/admin/roles/${editingRole.value.id}`, roleForm.value, {
      preserveScroll: true,
      onSuccess: () => {
        roleModalOpen.value = false;
      }
    });
  } else {
    router.post('/admin/roles', roleForm.value, {
      preserveScroll: true,
      onSuccess: () => {
        roleModalOpen.value = false;
      }
    });
  }
}

function deleteRole(role) {
  if (confirm(`Are you sure you want to delete the role "${role.name}"?`)) {
    router.delete(`/admin/roles/${role.id}`, {
      preserveScroll: true
    });
  }
}

function savePermission() {
  if (editingPermission.value) {
    router.put(`/admin/permissions/${editingPermission.value.id}`, permissionForm.value, {
      preserveScroll: true,
      onSuccess: () => {
        permissionModalOpen.value = false;
      }
    });
  } else {
    router.post('/admin/permissions', permissionForm.value, {
      preserveScroll: true,
      onSuccess: () => {
        permissionModalOpen.value = false;
      }
    });
  }
}

function deletePermission(permission) {
  if (confirm(`Are you sure you want to delete the permission "${permission.name}"?`)) {
    router.delete(`/admin/permissions/${permission.id}`, {
      preserveScroll: true
    });
  }
}

function saveUserRoles() {
  router.put(`/admin/users/${selectedUser.value.id}/roles`, userRolesForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      userRolesModalOpen.value = false;
    }
  });
}

const openUserModal = (user = null) => {
  editingUser.value = user;
  if (user) {
    userForm.name = user.name;
    userForm.email = user.email;
    userForm.roles = user.roles.map(role => role.id);
  } else {
    userForm.reset();
  }
  userModalOpen.value = true;
};

const closeUserModal = () => {
  userModalOpen.value = false;
  editingUser.value = null;
  userForm.reset();
};

const saveUser = () => {
  if (editingUser.value) {
    userForm.put(route('admin.users.update', editingUser.value.id), {
      onSuccess: () => closeUserModal()
    });
  } else {
    userForm.post(route('admin.users.store'), {
      onSuccess: () => closeUserModal()
    });
  }
};

const confirmDeleteUser = (user) => {
  userToDelete.value = user;
  deleteModalOpen.value = true;
};

const closeDeleteModal = () => {
  deleteModalOpen.value = false;
  userToDelete.value = null;
};

const deleteUser = () => {
  if (userToDelete.value) {
    useForm({}).delete(route('admin.users.destroy', userToDelete.value.id), {
      onSuccess: () => closeDeleteModal()
    });
  }
};

const restoreUser = (user) => {
  useForm({}).put(route('admin.users.restore', user.id));
};

const exportUsers = () => {
  // Convert users to CSV format
  const headers = ['name', 'email', 'password', 'roles'];
  const usersData = props.users.map(user => ({
    name: user.name,
    email: user.email,
    password: '12345678', // Default password
    roles: user.roles.map(role => role.name).join(';')
  }));

  // Create CSV content
  const csvContent = [
    headers.join(','),
    ...usersData.map(user => [
      `"${user.name}"`,
      `"${user.email}"`,
      `"${user.password}"`,
      `"${user.roles}"`
    ].join(','))
  ].join('\n');

  // Create and download the file
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);
  link.setAttribute('href', url);
  link.setAttribute('download', 'users_export.csv');
  link.style.visibility = 'hidden';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};
</script>