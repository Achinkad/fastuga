const httpServer = require('http').createServer()

const io = require("socket.io")(httpServer, {
    cors: {
        origin: "http://localhost:5173",
        methods: ["GET", "POST"],
        credentials: true
    }
})

httpServer.listen(8080, () =>{
    console.log('listening on *:8080')
})

io.on('connection', (socket) => {
    console.log(`client ${socket.id} has connected`)
})
