extends Node2D

@onready var pause_menu: CanvasLayer = $"Pause Menu"
@onready var transition: CanvasLayer = $Transition

var paused = false

func _process(delta: float) -> void:
	if Input.is_action_just_pressed("Esc"):
		pauseMenu()

func pauseMenu():
	if paused:
		pause_menu.hide()
		Engine.time_scale = 1
	else:
		pause_menu.show()
		Engine.time_scale = 0
	paused = !paused
