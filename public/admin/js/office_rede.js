$(document).ready(function(){
    var sys = arbor.ParticleSystem(1000, 600, 0.5) // create the system with sensible repulsion/stiffness/friction
    sys.parameters({gravity:true}) // use center-gravity to make the graph settle nicely (ymmv)
    sys.renderer = Renderer("#viewport") // our newly created renderer will have its .init() method called shortly by sys...

    // add some nodes to the graph and watch it go...
    sys.addEdge('a','b', {mass:.2})
    sys.addEdge('a','c', {mass:.2})
    sys.addEdge('a','d', {mass:.2})
    sys.addEdge('b','e', {mass:.3})
    sys.addEdge('b','f', {mass:.3})
    //sys.addNode('f', {alone:true, mass:.25})

    // or, equivalently:
    //
    // sys.graft({
    //   // nodes:{
    //   //   f:{alone:true, mass:.25}
    //   // },
    //   edges:{
    //     a:{
    //       mass:.1,
    //       b:{
    //          mass:.2
    //       },
    //       c:{
    //         mass:.2
    //       }
    //     }
    //   }
    // })

})