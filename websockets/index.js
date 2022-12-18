const http = require('http').createServer()

const io = require("socket.io")(http, {
    cors: {
        origin: "http://localhost:5173",
        methods: ["GET", "POST"],
        credentials: true
    }
})

http.listen(8080, () =>{
    console.log('listening on *:8080')
})

io.on('connection', (socket) => {
    console.log(`client ${socket.id} has connected`)
    socket.on('newOrder', (order) => {
        socket.broadcast.emit('newOrder', order)
    })

    socket.on('deleteOrder', (order) => {
        socket.broadcast.emit('deleteOrder', order)
    })
    socket.on('acceptedOrder', (order) => {
        socket.broadcast.emit('acceptedOrder', order)
    })
})
