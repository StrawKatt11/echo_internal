extends State


class_name GroundState

@export var jump_velocity = -400.0
@export var air_state : State

func state_input(event: InputEvent):
	if(event.is_action_pressed("Jump") && character.is_on_floor()):
		jump()

func jump():
	character.velocity.y = jump_velocity
	next_state = air_state
