extends Label

@export var state_machine : CharacterStateMachine

func _process(delta: float):
	text = "State: " + state_machine.current_state.name
