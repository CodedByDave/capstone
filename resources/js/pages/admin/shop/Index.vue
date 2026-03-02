<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import { ref, computed } from 'vue'

// shadcn components
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

// icons
import { Eye, Pencil, Trash2, Store, User, CheckCircle } from 'lucide-vue-next'

// Dialog
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog'

// AlertDialog
import {
    AlertDialog,
    AlertDialogTrigger,
    AlertDialogContent,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogDescription,
    AlertDialogFooter,
} from '@/components/ui/alert-dialog'

// Props
const { shops, stats } = defineProps<{
    shops: Array<{
        id: number
        shop_name: string
        phone: string
        block_street: string
        municipality: string
        barangay: string
        postal_code: string
        status: string
        owner: { name: string; email: string }
    }>
    stats: { today: number; total: number; active: number }
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Shop Management', href: '/admin/shop' },
]

// Filters
const searchQuery = ref('')
const statusFilter = ref('all')

// Dialog states
const selectedShop = ref<any | null>(null)
const shopToArchive = ref<any | null>(null)
const isViewOpen = ref(false)
const isEditOpen = ref(false)

// Edit form
const editForm = ref({
    shop_name: '',
    phone: '',
    block_street: '',
    municipality: '',
    barangay: '',
    postal_code: '',
    status: '',
})

// Computed
const filteredShops = computed(() =>
    shops.filter(shop => {
        const matchesSearch =
            shop.shop_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            shop.owner.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            shop.owner.email.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            shop.phone.toLowerCase().includes(searchQuery.value.toLowerCase())

        const matchesStatus =
            statusFilter.value === 'all' || shop.status === statusFilter.value

        return matchesSearch && matchesStatus
    })
)

// Actions
function openView(shop: any) {
    selectedShop.value = shop
    isViewOpen.value = true
}

function openEdit(shop: any) {
    selectedShop.value = shop
    editForm.value = {
        shop_name: shop.shop_name,
        phone: shop.phone,
        address: shop.address,
        business_license: shop.business_license,
        status: shop.status,
    }
    isEditOpen.value = true
}

function saveEdit() {
    console.log('Saving:', selectedShop.value.id, editForm.value)
    isEditOpen.value = false
}

function archiveShop(id: number | undefined) {
    if (!id) return
    console.log('Archiving shop:', id)
    shopToArchive.value = null
}
</script>

<template>

    <Head title="Shop Management" />

    <AdminLayout :breadcrumbs="breadcrumbs" title="Shop Management">

        <!-- STATS -->
        <div class="grid gap-4 md:grid-cols-3 mb-6">
            <Card>
                <CardHeader class="flex justify-between items-center">
                    <CardTitle>Registered Today</CardTitle>
                    <Store class="h-5 w-5 text-blue-600" />
                </CardHeader>
                <CardContent>
                    <p class="text-3xl font-bold text-blue-600">{{ stats.today }}</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex justify-between items-center">
                    <CardTitle>Total Shops</CardTitle>
                    <User class="h-5 w-5 text-purple-600" />
                </CardHeader>
                <CardContent>
                    <p class="text-3xl font-bold text-purple-600">{{ stats.total }}</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex justify-between items-center">
                    <CardTitle>Active Shops</CardTitle>
                    <CheckCircle class="h-5 w-5 text-green-600" />
                </CardHeader>
                <CardContent>
                    <p class="text-3xl font-bold text-green-600">{{ stats.active }}</p>
                </CardContent>
            </Card>
        </div>

        <!-- TABLE -->
        <Card>
            <CardHeader class="flex justify-between items-center">
                <CardTitle>Shop List</CardTitle>

                <div class="flex gap-2">
                    <Input v-model="searchQuery" placeholder="Search..." class="max-w-xs" />
                    <Select v-model="statusFilter">
                        <SelectTrigger class="w-32">
                            <SelectValue placeholder="Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All</SelectItem>
                            <SelectItem value="active">Active</SelectItem>
                            <SelectItem value="inactive">Inactive</SelectItem>
                            <SelectItem value="pending">Pending</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </CardHeader>

            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Shop Name</TableHead>
                            <TableHead>Owner</TableHead>
                            <TableHead>Phone</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Block/Street</TableHead>
                            <TableHead>Municipality</TableHead>
                            <TableHead>Barangay</TableHead>
                            <TableHead>Postal Code</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Actions</TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <TableRow v-for="shop in filteredShops" :key="shop.id">
                            <TableCell>{{ shop.shop_name }}</TableCell>
                            <TableCell>{{ shop.owner.name }}</TableCell>
                            <TableCell>{{ shop.phone }}</TableCell>
                            <TableCell>{{ shop.owner.email }}</TableCell>
                            <TableCell>{{ shop.block_street }}</TableCell>
                            <TableCell>{{ shop.municipality }}</TableCell>
                            <TableCell>{{ shop.barangay }}</TableCell>
                            <TableCell>{{ shop.postal_code }}</TableCell>

                            <TableCell>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full text-white" :class="{
                                    'bg-green-500': shop.status === 'active',
                                    'bg-red-500': shop.status === 'inactive',
                                    'bg-yellow-500': shop.status === 'pending'
                                }">
                                    {{ shop.status }}
                                </span>
                            </TableCell>

                            <TableCell class="text-center space-x-2">
                                <Button size="icon" variant="ghost" @click="openView(shop)">
                                    <Eye class="h-5 w-5 text-blue-500" />
                                </Button>

                                <Button size="icon" variant="ghost" @click="openEdit(shop)">
                                    <Pencil class="h-5 w-5 text-green-500" />
                                </Button>

                                <AlertDialog>
                                    <AlertDialogTrigger asChild>
                                        <Button size="icon" variant="ghost" @click="shopToArchive = shop">
                                            <Trash2 class="h-5 w-5 text-red-600" />
                                        </Button>
                                    </AlertDialogTrigger>

                                    <AlertDialogContent>
                                        <AlertDialogHeader>
                                            <AlertDialogTitle>Archive Shop</AlertDialogTitle>
                                            <AlertDialogDescription>
                                                Archive <strong>{{ shopToArchive?.shop_name }}</strong>?
                                            </AlertDialogDescription>
                                        </AlertDialogHeader>
                                        <AlertDialogFooter>
                                            <Button variant="outline" @click="shopToArchive = null">Cancel</Button>
                                            <Button variant="destructive" @click="archiveShop(shopToArchive?.id)">
                                                Archive
                                            </Button>
                                        </AlertDialogFooter>
                                    </AlertDialogContent>
                                </AlertDialog>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>

        <!-- VIEW DIALOG (GRID) -->
        <Dialog v-model:open="isViewOpen">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Shop Details</DialogTitle>
                </DialogHeader>

                <div v-if="selectedShop" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div><strong>Shop Name</strong>
                        <p>{{ selectedShop.shop_name }}</p>
                    </div>
                    <div><strong>Owner</strong>
                        <p>{{ selectedShop.owner.name }}</p>
                    </div>
                    <div><strong>Email</strong>
                        <p>{{ selectedShop.owner.email }}</p>
                    </div>
                    <div><strong>Phone</strong>
                        <p>{{ selectedShop.phone }}</p>
                    </div>
                    <div><strong>Address</strong>
                        <p>{{ selectedShop.address }}</p>
                    </div>
                    <div><strong>License</strong>
                        <p>{{ selectedShop.business_license }}</p>
                    </div>
                    <div><strong>Status</strong>
                        <p>{{ selectedShop.status }}</p>
                    </div>
                    <div><strong>Created</strong>
                        <p>{{ selectedShop.created_at }}</p>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="isViewOpen = false">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- EDIT DIALOG -->
        <Dialog v-model:open="isEditOpen">
            <DialogContent class="max-w-3xl">
                <DialogHeader>
                    <DialogTitle>Edit Laundry / Shop</DialogTitle>
                </DialogHeader>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <!-- Laundry / Shop Name (FULL WIDTH) -->
                    <div class="space-y-1 md:col-span-2">
                        <label class="text-sm font-medium">Laundry Name</label>
                        <Input v-model="editForm.shop_name" />
                    </div>

                    <!-- Owner Name (FULL WIDTH, READ-ONLY) -->
                    <div class="space-y-1 md:col-span-2">
                        <label class="text-sm font-medium">Owner Name</label>
                        <Input :model-value="selectedShop?.owner?.name" disabled />
                    </div>

                    <!-- Phone (SHORT) -->
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Phone Number</label>
                        <Input v-model="editForm.phone" />
                    </div>

                    <!-- Status (SHORT) -->
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Status</label>
                        <Select v-model="editForm.status">
                            <SelectTrigger>
                                <SelectValue placeholder="Select status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="inactive">Inactive</SelectItem>
                                <SelectItem value="pending">Pending</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <DialogFooter class="mt-6">
                    <Button variant="outline" @click="isEditOpen = false">
                        Cancel
                    </Button>
                    <Button @click="saveEdit">
                        Save Changes
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

    </AdminLayout>
</template>
