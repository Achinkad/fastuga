import { createRouter, createWebHistory } from 'vue-router'
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
      props: { onlyCurrentOrders: true, ordersTittle: 'Current Order' }
    },
    {
      path: '/order/new',
      name: 'NewOrder',
      component: Order,
      props: { id: -1}
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
      path: '/about',
      name: 'about',
    
      component: () => import('../views/AboutView.vue')
    }
  ]
})

export default router
