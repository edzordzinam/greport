class Ship extends MovieClip {
  var velocity = 10;

  function onEnterFrame() {
    if( Key.isDown( Key.UP    ) ) _y -= velocity;
    if( Key.isDown( Key.DOWN  ) ) _y += velocity;
    if( Key.isDown( Key.LEFT  ) ) _x -= velocity;
    if( Key.isDown( Key.RIGHT ) ) _x += velocity;
  }

}
