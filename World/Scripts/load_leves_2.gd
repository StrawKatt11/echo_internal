extends Area2D

func _on_body_entered(body: Node2D) -> void:
	if body.is_in_group("player"):
		get_tree().change_scene_to_file("res://World/Scenes/Levels/Lv2.tscn")
		GlobalTime.timelord += Stopwatch.elapsed_time
		Stopwatch.reset()
