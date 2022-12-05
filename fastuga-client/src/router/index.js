import { createRouter, createWebHistory } from 'vue-router'
import { useUserStore } from "../stores/user.js"

import HomeView from '../views/HomeView.vue'
import Dashboard from "../components/Dashboard.vue"
import Orders from "../components/orders/Orders.vue"
import Order from "../components/orders/Order.vue"
import ChangePassword from "../components/auth/ChangePassword.vue"
import Login from "../components/auth/Login.vue"
import Users from "../components/users/Users.vue"
import User from "../components/users/User.vue"
import Register from "../components/auth/Register.vue"
import Products from "../components/products/Products.vue"
import Product from "../components/products/Product.vue"

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
        {
            path: '/',
            name: 'home',
            component: HomeView
        },
        {
            path: '/users',
            name: 'Users',
            component: Users,
        },
        {
            path: '/products',
            name: 'Products',
            component: Products,
        },
        {
            path: '/users/:id',
            name: 'User',
            component: User,
            props: route => ({ id: parseInt(route.params.id) })
        },
        {
            path: '/login',
            name: 'Login',
            component: Login
        },
        {
            path: '/password',
            name: 'ChangePassword',
            component: ChangePassword
        },
        {
            path: '/register',
            name: 'Register',
            component: Register
        },
        {
            path: '/orders',
            name: 'Orders',
            component: Orders,
        },
        {
            path: '/orders/current',
            name: 'CurrentOrder',
            component: Orders,
            props: { onlyCurrentOrders: true, ordersTitle: 'Current Order' }
        },
        {
            path: '/order/new',
            name: 'NewOrder',
            component: Order,
            props: { id: -1 }
        },
        {
            path: '/products/new',
            name: 'newProduct',
            component: Product,
            props: { id: -1 }
        },
        {
            path: '/dashboard',
            name: 'Dashboard',
            component: Dashboard
        },
        {
            path: '/orders/:id',
            name: 'Order',
            component: Order,
            props: route => ({ id: parseInt(route.params.id) })
        },
        {
            path: '/products/:id',
            name: 'Product',
            component: Product,
            props: route => ({ id: parseInt(route.params.id) })
        },
        {
            path: '/:pathMatch(.*)*',
            name: 'Forbidden',
            component: () => import('@/views/Forbidden.vue')
        },
    ]
})

let handlingFirstRoute = true

router.beforeEach(async (to, from, next) => {
    const userStore = useUserStore()
    if (handlingFirstRoute) {
        handlingFirstRoute = false
        await userStore.restoreToken()
    }

    if ((to.name == 'Login')) {
        next()
        return
    }

    if (!userStore.user) {
        next({ name: 'Login' })
        return
    }

    if (to.name == 'Products') {
        if (userStore.user.type != 'EM') {
            next({
                name: 'Forbidden',
                params: { pathMatch: to.path.substring(1).split('/') },
                query: to.query,
                hash: to.hash
            })
            return
        }
    }

    if (to.name == 'User') {
        if ((userStore.user.type == 'A') || (userStore.user.id == to.params.id)) {
            next()
            return
        }
        next({ name: 'home' })
        return
    }

    next()
})

export default router
