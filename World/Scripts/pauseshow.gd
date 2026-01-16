extends CanvasLayer

@onready var pause_menu: CanvasLayer = $"Pause Menu"
@onready var transition = get_node("/root/Transition")

var paused = false

func _process(delta: float) -> void:
	if Input.is_action_just_pressed("Esc"):
		pauseMenu()

func pauseMenu():
	if paused:
		hide()
		Engine.time_scale = 1
	else:
		show()
		Engine.time_scale = 0
	paused = !paused
