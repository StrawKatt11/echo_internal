extends CanvasLayer

signal transitioned
@onready var animation: AnimationPlayer = $AnimationPlayer

func transition():
	animation.play("Fade_To_Black")

func _on_animation_player_animation_finished(anim_name: StringName) -> void:
	if anim_name == "Fade_To_Black":
		emit_signal("transitioned")
		animation.play("Fade_To_Normal")
	if anim_name == "Fade_To_Normal":
		print("Finished trans")
