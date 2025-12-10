extends Control

var paused = true

func _ready():
	await get_tree().physics_frame

func _process(delta: float) -> void:
	pass

func _on_resume_pressed() -> void:
	pass


func _on_restart_pressed() -> void:
	get_tree().reload_current_scene()
	Engine.time_scale = 1


func _on_main_menu_pressed() -> void:
	get_tree().change_scene_to_file("res://World/Scenes/title_screen.tscn")
	Engine.time_scale = 1


func _on_quit_pressed() -> void:
	get_tree().quit()
